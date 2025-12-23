# Implementation Plan: Medical Records System

## Overview

Implementation of a medical records system to document patient evolution over time. Professionals can create, view, edit and delete medical session records. System follows existing Maiêutica patterns with permission-based authorization and soft deletes.

**Supported Patients:**
- **Kids** (children up to 6 years - Denver assessment)
- **Users** (patients over 6 years old and adults)

## Design Decisions (Confirmed with User)

- ✅ Patient data (name, birth date) **derived from model** via polymorphic relationship
- ✅ Professional is always the **creator** (created_by) - not editable
- ✅ Referral/Closure is a **single field** (textarea)
- ✅ **Polymorphic** support for Kids AND Users as patients (patient_id + patient_type)

## Critical Business Rules

### Authorization (Different from Kids/Checklists)

**Professionals can:**
- ✅ **VIEW** medical records of ALL assigned patients (Kids or Users, even created by others)
- ✅ **CREATE** medical records for their assigned patients (Kids or Users)
- ⚠️ **EDIT/DELETE** ONLY medical records they created themselves (created_by check)

**Admin can:**
- ✅ View, create, edit, delete ALL medical records

**Rationale:** Professionals need to see complete patient history (visibility), but can only modify their own notes (integrity).

**Important:** Kids maintain relationship with Professionals via `kid_professional` pivot. Users (adult patients) will have similar or direct relationship with Professional (to be defined in implementation).

---

## 1. DATA LAYER

### Migration: `create_medical_records_table.php`

```php
Schema::create('medical_records', function (Blueprint $table) {
    $table->id();

    // Polymorphic relationship to patient (Kid or User)
    $table->morphs('patient'); // Creates patient_id and patient_type

    // Session fields
    $table->date('session_date'); // Session date
    $table->text('complaint'); // Patient complaint/demand
    $table->text('objective_technique'); // Objective and technique used
    $table->text('evolution_notes'); // Evolution/progress notes (main field)
    $table->text('referral_closure')->nullable(); // Referral OR closure notes

    // Audit trail
    $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
    $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
    $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');

    $table->timestamps();
    $table->softDeletes();

    // Performance indexes
    $table->index('created_by');
    $table->index('session_date');
    $table->index(['patient_id', 'patient_type']); // Morph index
    $table->index(['patient_id', 'patient_type', 'session_date']); // Composite for filters
});
```

**Important:**
- `morphs('patient')` creates `patient_id` (unsignedBigInteger) + `patient_type` (string) with indexes
- **patient_type** will store: `App\Models\Kid` or `App\Models\User`
- When Kid/User deleted, **no** automatic CASCADE (Laravel doesn't support CASCADE on morphs)
- Will need to implement **event listeners** or **deleting observers** to clean orphaned records
- `created_by` RESTRICT: cannot delete user who created medical records
- Composite index `(patient_id, patient_type, session_date)` essential for listing queries

---

## 2. MODELS

### `app/Models/MedicalRecord.php`

**Extends:** `BaseModel` (includes SoftDeletes)

**Fillable:**
```php
['patient_id', 'patient_type', 'session_date', 'complaint', 'objective_technique',
 'evolution_notes', 'referral_closure', 'created_by', 'updated_by', 'deleted_by']
```

**Relationships:**
```php
// Polymorphic relationship
public function patient()
{
    return $this->morphTo();
}

// Audit trail
public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}

public function updater()
{
    return $this->belongsTo(User::class, 'updated_by');
}

public function deleter()
{
    return $this->belongsTo(User::class, 'deleted_by');
}
```

**Date Handling (Brazilian Standard):**
```php
// Accessor: Y-m-d (DB) → d/m/Y (display)
public function getSessionDateAttribute($value)
{
    if (!$value) return null;
    return \Carbon\Carbon::parse($value)->format('d/m/Y');
}

// Mutator: d/m/Y or Y-m-d (input) → Y-m-d (DB)
public function setSessionDateAttribute($value)
{
    if (!$value) return;

    if (str_contains($value, '/')) {
        // Brazilian format d/m/Y
        $this->attributes['session_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
    } else {
        // ISO format Y-m-d
        $this->attributes['session_date'] = \Carbon\Carbon::parse($value)->format('Y-m-d');
    }
}
```

**Display Accessors:**
```php
// Patient name (works for Kid or User)
public function getPatientNameAttribute()
{
    return $this->patient ? $this->patient->name : 'N/A';
}

// Readable patient type
public function getPatientTypeNameAttribute()
{
    return match($this->patient_type) {
        'App\Models\Kid' => 'Criança',
        'App\Models\User' => 'Adulto',
        default => 'Desconhecido',
    };
}
```

**Scopes:**
```php
// Filter by authenticated professional (Kids AND Users assigned)
public function scopeForAuthProfessional($query)
{
    $user = auth()->user();
    $professional = $user->professional->first();

    if (!$professional) {
        return $query->whereRaw('1 = 0'); // Return empty
    }

    return $query->where(function ($q) use ($professional) {
        // Medical records of Kids assigned to professional
        $q->where(function ($subQ) use ($professional) {
            $subQ->where('patient_type', 'App\Models\Kid')
                ->whereHas('patient.professionals', function ($kidQ) use ($professional) {
                    $kidQ->where('professional_id', $professional->id);
                });
        })
        // OR medical records of Users (adult patients) assigned
        // TODO: implement User->Professional assignment logic when defined
        ->orWhere(function ($subQ) use ($professional) {
            $subQ->where('patient_type', 'App\Models\User');
            // Add whereHas when User->Professional relationship is implemented
        });
    });
}

// Filter by specific patient
public function scopeForPatient($query, $patientId, $patientType)
{
    return $query->where('patient_id', $patientId)
                 ->where('patient_type', $patientType);
}
```

**Reference:** `Kid.php` (lines 1-120) for date handling pattern

### Add to `app/Models/Kid.php`

```php
public function medicalRecords()
{
    return $this->morphMany(MedicalRecord::class, 'patient')->orderBy('session_date', 'desc');
}

// Observer to delete medical records when Kid is deleted
protected static function booted()
{
    static::deleting(function ($kid) {
        $kid->medicalRecords()->delete(); // Soft delete medical records
    });
}
```

### Add to `app/Models/User.php`

```php
public function medicalRecords()
{
    return $this->morphMany(MedicalRecord::class, 'patient')->orderBy('session_date', 'desc');
}

// Observer to delete medical records when User patient is deleted
// NOTE: Only for Users who are patients (not all users)
protected static function booted()
{
    static::deleting(function ($user) {
        $user->medicalRecords()->delete(); // Soft delete medical records
    });
}
```

---

## 3. AUTHORIZATION

### Permissions (9 total)

**Base (professionals):**
- `medical-record-list` - List for assigned patients (Kids or Users)
- `medical-record-show` - View details
- `medical-record-create` - Create new
- `medical-record-edit` - Edit own (created_by)
- `medical-record-delete` - Delete own (created_by)

**Admin:**
- `medical-record-list-all` - View all
- `medical-record-show-all` - View any
- `medical-record-edit-all` - Edit any
- `medical-record-delete-all` - Delete any

### `app/Policies/MedicalRecordPolicy.php`

**Critical Pattern:**

```php
public function viewAny(User $user): bool
{
    return $user->can('medical-record-list') || $user->can('medical-record-list-all');
}

public function view(User $user, MedicalRecord $medicalRecord): bool
{
    // Admin sees everything
    if ($user->can('medical-record-show-all')) return true;

    // Professional sees if they created it
    if ($user->can('medical-record-show')) {
        if ($medicalRecord->created_by === $user->id) return true;

        // OR if patient is assigned to them
        $professional = $user->professional->first();
        if ($professional && $medicalRecord->patient) {
            // If Kid, check professionals pivot
            if ($medicalRecord->patient_type === 'App\Models\Kid') {
                if ($medicalRecord->patient->professionals->contains($professional->id)) {
                    return true;
                }
            }

            // If User (adult patient), check assignment
            // TODO: implement when User->Professional relationship is defined
            if ($medicalRecord->patient_type === 'App\Models\User') {
                // Temporarily allow viewing for any professional
                // Adjust when User->Professional assignment system is implemented
                return true;
            }
        }
    }

    return false;
}

public function update(User $user, MedicalRecord $medicalRecord): bool
{
    // Admin can edit everything
    if ($user->can('medical-record-edit-all')) return true;

    // Professional ONLY if they created it
    if ($user->can('medical-record-edit')) {
        return $medicalRecord->created_by === $user->id;
    }

    return false;
}

public function delete(User $user, MedicalRecord $medicalRecord): bool
{
    // Admin can delete everything
    if ($user->can('medical-record-delete-all')) return true;

    // Professional ONLY if they created it
    if ($user->can('medical-record-delete')) {
        return $medicalRecord->created_by === $user->id;
    }

    return false;
}

public function viewTrash(User $user): bool
{
    return $user->can('medical-record-list-all'); // Admin only
}

public function restore(User $user, MedicalRecord $medicalRecord): bool
{
    if ($user->can('medical-record-edit-all')) return true;

    if ($user->can('medical-record-edit')) {
        return $medicalRecord->created_by === $user->id;
    }

    return false;
}
```

**Reference:** `KidPolicy.php`, `UserPolicy.php`

### Seeder: `database/seeders/RoleAndPermissionSeeder.php`

**Add to `$permissions` array (line ~113):**
```php
// Medical Records
'medical-record-list',
'medical-record-show',
'medical-record-create',
'medical-record-edit',
'medical-record-delete',
'medical-record-list-all',
'medical-record-show-all',
'medical-record-edit-all',
'medical-record-delete-all',
```

**Add to `$permissionsProfissional` (line ~164):**
```php
'medical-record-list',
'medical-record-show',
'medical-record-create',
'medical-record-edit',
'medical-record-delete',
```

**Execute:** `php artisan db:seed --class=RoleAndPermissionSeeder`

### Register Policy: `app/Providers/AuthServiceProvider.php`

```php
use App\Models\MedicalRecord;
use App\Policies\MedicalRecordPolicy;

protected $policies = [
    // ...
    MedicalRecord::class => MedicalRecordPolicy::class,
];
```

---

## 4. CONTROLLER

### `app/Http/Controllers/MedicalRecordsController.php`

**Constructor:**
```php
protected $medicalRecordLogger;

public function __construct(MedicalRecordLogger $medicalRecordLogger)
{
    $this->medicalRecordLogger = $medicalRecordLogger;
}
```

**Methods (9):**

1. **index(Request $request)**
   - `$this->authorize('viewAny', MedicalRecord::class)`
   - Eager load: `with(['patient', 'creator'])`
   - Filters: patient_type, patient_id, date_start/date_end, search
   - **CRITICAL - Professional scope:**
     ```php
     if (!auth()->user()->can('medical-record-list-all')) {
         $query->forAuthProfessional(); // Use model scope
     }
     ```
   - Paginate: `self::PAGINATION_DEFAULT`
   - Log: `$this->medicalRecordLogger->listed()`

2. **create()**
   - `$this->authorize('create', MedicalRecord::class)`
   - `$kids = $this->getKidsForUser()` (helper)
   - `$users = $this->getUserPatientsForUser()` (helper - adult patients)

3. **store(MedicalRecordRequest $request)**
   - `$this->authorize('create', MedicalRecord::class)`
   - `DB::beginTransaction()`
   - `$data['created_by'] = auth()->id()`
   - `MedicalRecord::create($data)`
   - Log: `$this->medicalRecordLogger->created()`
   - `DB::commit()`
   - Redirect: `route('medical-records.index')`

4. **show(MedicalRecord $medicalRecord)**
   - `$this->authorize('view', $medicalRecord)`
   - Load: `patient`, `creator`
   - If patient is Kid: load `patient.responsible`, `patient.professionals.user`
   - Log: `$this->medicalRecordLogger->viewed()`

5. **edit(MedicalRecord $medicalRecord)**
   - `$this->authorize('update', $medicalRecord)`
   - `$kids = $this->getKidsForUser()`
   - `$users = $this->getUserPatientsForUser()`

6. **update(MedicalRecordRequest $request, MedicalRecord $medicalRecord)**
   - `$this->authorize('update', $medicalRecord)`
   - `DB::beginTransaction()`
   - Track changes for logging
   - `$data['updated_by'] = auth()->id()`
   - `$medicalRecord->update($data)`
   - Log: `$this->medicalRecordLogger->updated($medicalRecord, $changes)`
   - `DB::commit()`

7. **destroy(MedicalRecord $medicalRecord)**
   - `$this->authorize('delete', $medicalRecord)`
   - `$medicalRecord->deleted_by = auth()->id()`
   - `$medicalRecord->save()`
   - `$medicalRecord->delete()` (soft)
   - Log: `$this->medicalRecordLogger->deleted()`

8. **trash()**
   - `$this->authorize('viewTrash', MedicalRecord::class)`
   - `MedicalRecord::onlyTrashed()->with(['patient', 'creator', 'deleter'])`

9. **restore($id)**
   - `$medicalRecord = MedicalRecord::onlyTrashed()->findOrFail($id)`
   - `$this->authorize('restore', $medicalRecord)`
   - `$medicalRecord->restore()`
   - Log: `$this->medicalRecordLogger->restored()`

**Private Helpers:**
```php
private function getKidsForUser()
{
    if (auth()->user()->can('medical-record-list-all')) {
        return Kid::orderBy('name')->get();
    }

    $professional = auth()->user()->professional->first();
    return Kid::whereHas('professionals', function ($q) use ($professional) {
        $q->where('professional_id', $professional->id);
    })->orderBy('name')->get();
}

private function getUserPatientsForUser()
{
    // TODO: Implement when User->Professional relationship is defined
    // For now, returns users who are not professionals or admin

    if (auth()->user()->can('medical-record-list-all')) {
        // Admin sees all users (filter to patients only if needed)
        return User::where('allow', 1) // Active
                   ->orderBy('name')
                   ->get();
    }

    // Professional sees only their assigned user patients
    // Temporarily returns empty until assignment system is implemented
    return collect([]);
}
```

**Reference:** `KidsController.php` (lines 23-501)

---

## 5. VALIDATION

### `app/Http/Requests/MedicalRecordRequest.php`

```php
public function rules(): array
{
    return [
        'patient_type' => 'required|in:App\Models\Kid,App\Models\User',
        'patient_id' => [
            'required',
            'integer',
            // Conditional validation: checks if ID exists in corresponding table
            function ($attribute, $value, $fail) {
                $type = $this->input('patient_type');
                if ($type === 'App\Models\Kid') {
                    if (!Kid::find($value)) {
                        $fail('O paciente (criança) selecionado não existe.');
                    }
                } elseif ($type === 'App\Models\User') {
                    if (!User::find($value)) {
                        $fail('O paciente (adulto) selecionado não existe.');
                    }
                }
            },
        ],
        'session_date' => 'required|date_format:d/m/Y|before_or_equal:today',
        'complaint' => 'required|string|min:10|max:5000',
        'objective_technique' => 'required|string|min:10|max:5000',
        'evolution_notes' => 'required|string|min:10|max:10000',
        'referral_closure' => 'nullable|string|max:5000',
    ];
}

public function attributes(): array
{
    return [
        'patient_type' => 'Tipo de Paciente',
        'patient_id' => 'Paciente',
        'session_date' => 'Data',
        'complaint' => 'Demanda',
        'objective_technique' => 'Objetivo/Técnica',
        'evolution_notes' => 'Registro de Evolução',
        'referral_closure' => 'Encaminhamento ou Encerramento',
    ];
}

public function messages(): array
{
    return [
        'patient_type.required' => 'O tipo de paciente é obrigatório.',
        'patient_type.in' => 'Tipo de paciente inválido.',
        'patient_id.required' => 'O paciente é obrigatório.',
        'patient_id.integer' => 'ID do paciente deve ser um número.',
    ];
}
```

**Important Validations:**
- `patient_type`: must be `App\Models\Kid` or `App\Models\User`
- `patient_id`: conditional validation checks existence in correct table
- `session_date`: Brazilian format (d/m/Y), cannot be future
- Minimum 10 characters for text fields (ensures meaningful content)
- `referral_closure`: nullable (optional)

**Reference:** `KidRequest.php`

---

## 6. ROUTES

### `routes/web.php`

**Add after checklists routes (~line 58):**

```php
// Medical Records
Route::middleware(['auth'])->group(function () {
    Route::get('medical-records/trash', [MedicalRecordsController::class, 'trash'])
        ->name('medical-records.trash');
    Route::post('medical-records/{id}/restore', [MedicalRecordsController::class, 'restore'])
        ->name('medical-records.restore');
    Route::resource('medical-records', MedicalRecordsController::class);
});
```

**Import at top:**
```php
use App\Http\Controllers\MedicalRecordsController;
```

⚠️ **CRITICAL ORDER:** trash/restore BEFORE resource() to avoid route conflicts

---

## 7. VIEWS

Create 5 files in `resources/views/medical-records/`

### 1. `index.blade.php` - Listing

**Components:**
- Breadcrumb: "Medical Records" / "Prontuários"
- Actions: "New Record" button (`@can('medical-record-create')`), "Trash" (`@can('medical-record-list-all')`)
- Filters:
  - Patient Type dropdown (All, Child, Adult)
  - Patient dropdown (Kids + Users, filtered by selected type)
  - Date range (date_start/date_end) with jQuery mask
  - Search field
- Table:
  - Columns: Date, Type, Patient, Complaint (truncated), Created by, Created at, Actions
  - **Type Column:** Badge with "Criança" or "Adulto"
  - Actions per row: View (eye), Edit (`@can('update', $record)`), Delete (`@can('delete', $record)`)
- Pagination

**Pattern:** `kids/index.blade.php`

### 2. `create.blade.php` - Creation

**Form fields:**
- **Patient Type** (radio buttons or select) - `required`
  - Option: Child (App\Models\Kid)
  - Option: Adult (App\Models\User)
- **Patient** (dynamic select dropdown based on type) - `required`
  - If type=Kid: shows Kids dropdown (`$kids`)
  - If type=User: shows Users dropdown (`$users`)
- Session Date (input date with mask dd/mm/yyyy) - `required`
- Complaint (textarea, 4 rows) - `required`
- Objective/Technique (textarea, 4 rows) - `required`
- Evolution Notes (textarea, 6 rows) - `required`
- Referral/Closure (textarea, 4 rows) - `nullable`

**Validation:** `@error` directives on each field

**JavaScript:**
- jQuery mask for date: `$('#session_date').mask('00/00/0000')`
- Toggle patient dropdown based on selected type
```javascript
$('#patient_type').change(function() {
    const type = $(this).val();
    $('.patient-dropdown').hide();
    if (type === 'App\\Models\\Kid') {
        $('#kid-dropdown').show();
    } else if (type === 'App\\Models\\User') {
        $('#user-dropdown').show();
    }
});
```

**Pattern:** `kids/create.blade.php`

### 3. `edit.blade.php` - Editing

Same as create, but:
- Pre-fill with `old()` or model data
- Form action: PUT to update route
- Button: "Update"

### 4. `show.blade.php` - Viewing

**Header:**
- Badge with patient type (Child/Adult)
- Patient name

**Layout 2 columns:**
- **Left - Patient info:**
  - **If Kid:** Name, Birth date, Age, Guardian, Professionals
  - **If User:** Name, Email, Birth date (if exists), Phone
- **Right - Session info:**
  - Session date
  - Created by (professional)
  - Created at
  - Updated at (if exists)

**Sections (full width):**
- Complaint
- Objective/Technique
- Evolution Notes
- Referral/Closure (if filled)

**Actions:**
- Edit (`@can('update', $medicalRecord)`)
- Delete (`@can('delete', $medicalRecord)`) with confirmation
- Back to listing

**Blade conditional:**
```blade
@if ($medicalRecord->patient_type === 'App\Models\Kid')
    {{-- Kid specific data --}}
@else
    {{-- User specific data --}}
@endif
```

### 5. `trash.blade.php` - Trash

- Table with deleted records
- Columns: Date, Type, Patient, Complaint (truncated), Deleted by, Deleted at, Actions
- **Type Column:** Badge with "Criança" or "Adulto"
- Action: Restore (`@can('restore', $record)`)
- Empty state if empty

**Pattern:** `kids/trash.blade.php`

---

## 8. LOGGING (LGPD Compliant)

### `app/Services/Logging/MedicalRecordLogger.php`

**Methods:**
- `created($medicalRecord, $context)` - notice
- `updated($medicalRecord, $changes, $context)` - notice
- `deleted($medicalRecord, $context)` - notice
- `restored($medicalRecord, $context)` - notice
- `viewed($medicalRecord, $context)` - info
- `listed($context)` - debug
- `trashViewed($context)` - info
- `operationFailed($operation, $exception, $context)` - error

**LGPD Sanitization:**
```php
private function sanitizeChanges(array $changes): array
{
    $sensitiveFields = ['complaint', 'objective_technique', 'evolution_notes', 'referral_closure'];
    foreach ($sensitiveFields as $field) {
        if (isset($changes[$field])) {
            $changes[$field] = '[CHANGED]'; // Don't log medical content
        }
    }
    return $changes;
}

private function getPatientIdentifier($medicalRecord): string
{
    if (!$medicalRecord->patient) return '[UNKNOWN PATIENT]';

    // For Kids, use initials
    if ($medicalRecord->patient_type === 'App\Models\Kid') {
        return $medicalRecord->patient->initials ?? '[KID]';
    }

    // For Users, use only ID (don't expose name)
    if ($medicalRecord->patient_type === 'App\Models\User') {
        return '[USER-' . $medicalRecord->patient_id . ']';
    }

    return '[PATIENT]';
}
```

- Uses secure patient identifier (Kid->initials or User-ID)
- Never logs full name or medical field contents
- Includes user context (id, name, email, IP)
- Log includes patient type (Kid/User) but no sensitive data

**Reference:** `KidLogger.php`

---

## 9. MENU

### `resources/views/layouts/menu.blade.php`

**Add after "Documents" dropdown (~line 158):**

```blade
@can('medical-record-list')
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle @if (request()->is('medical-records*')) active @endif"
           href="#"
           id="medicalRecordsDropdown"
           role="button"
           data-bs-toggle="dropdown"
           aria-expanded="false">
            <i class="bi bi-file-medical"></i> Prontuários
        </a>
        <ul class="dropdown-menu" aria-labelledby="medicalRecordsDropdown">
            <li>
                <a class="dropdown-item" href="{{ route('medical-records.index') }}">
                    <i class="bi bi-list"></i> Listar Prontuários
                </a>
            </li>
            @can('medical-record-create')
                <li>
                    <a class="dropdown-item" href="{{ route('medical-records.create') }}">
                        <i class="bi bi-plus-circle"></i> Novo Prontuário
                    </a>
                </li>
            @endcan
            @can('medical-record-list-all')
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="{{ route('medical-records.trash') }}">
                        <i class="bi bi-trash"></i> Lixeira
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcan
```

**Icon:** `bi-file-medical` (Bootstrap Icons)

---

## IMPLEMENTATION SEQUENCE

**Recommended order (prevents dependency errors):**

1. ✅ Migration → `php artisan migrate`
2. ✅ Models (MedicalRecord + relations in Kid and User)
3. ✅ Policy → Register in AuthServiceProvider
4. ✅ Permissions → Seeder → `php artisan db:seed --class=RoleAndPermissionSeeder`
5. ✅ Request validation
6. ✅ Logger service
7. ✅ Controller
8. ✅ Routes
9. ✅ Views (all 5)
10. ✅ Menu integration
11. ✅ Manual testing

---

## FILES TO CREATE/MODIFY

### Create (11 new files):

1. `database/migrations/YYYY_MM_DD_HHMMSS_create_medical_records_table.php`
2. `app/Models/MedicalRecord.php`
3. `app/Policies/MedicalRecordPolicy.php`
4. `app/Http/Controllers/MedicalRecordsController.php`
5. `app/Http/Requests/MedicalRecordRequest.php`
6. `app/Services/Logging/MedicalRecordLogger.php`
7. `resources/views/medical-records/index.blade.php`
8. `resources/views/medical-records/create.blade.php`
9. `resources/views/medical-records/edit.blade.php`
10. `resources/views/medical-records/show.blade.php`
11. `resources/views/medical-records/trash.blade.php`

### Modify (5 existing files):

1. `app/Models/Kid.php` - add `medicalRecords()` morphMany + observer
2. `app/Models/User.php` - add `medicalRecords()` morphMany + observer
3. `database/seeders/RoleAndPermissionSeeder.php` - add permissions
4. `app/Providers/AuthServiceProvider.php` - register policy
5. `routes/web.php` - add routes
6. `resources/views/layouts/menu.blade.php` - add menu item

---

## TEST CHECKLIST

### Authorization
- [ ] Admin sees ALL medical records (Kids and Users)
- [ ] Professional sees ONLY records of assigned patients (Kids or Users)
- [ ] Professional can create record for assigned Kid
- [ ] Professional can create record for assigned User patient
- [ ] Professional CANNOT create for unassigned patient
- [ ] Professional can edit ONLY their own records (created_by)
- [ ] Professional CANNOT edit another's record (same patient)
- [ ] Professional can delete ONLY their own records
- [ ] Only admin sees trash
- [ ] Professional can restore ONLY their own records

### Functionality
- [ ] Create record for Kid saves correctly
- [ ] Create record for User saves correctly
- [ ] Update record saves changes
- [ ] Soft delete moves to trash
- [ ] Restore works
- [ ] Filters work (patient type, patient, date, search)
- [ ] Pagination works
- [ ] Patient dropdown filters by professional
- [ ] Patient dropdown changes based on selected type (Kid/User)
- [ ] Date accepts dd/mm/yyyy format
- [ ] Date rejects future dates
- [ ] patient_type validates correctly (only Kid or User)
- [ ] patient_id validates existence in correct table

### UI
- [ ] Menu appears with correct permission
- [ ] Breadcrumbs correct
- [ ] Flash messages appear
- [ ] Validation errors displayed
- [ ] Date mask works
- [ ] Delete confirmation appears
- [ ] Table responsive on mobile

### Integration
- [ ] Logs appear in `storage/logs/laravel.log`
- [ ] Deleting Kid deletes its records (observer)
- [ ] Deleting User patient deletes its records (observer)
- [ ] Deleting user who created records is blocked (restrict)
- [ ] Eager loading avoids N+1 queries
- [ ] Polymorphic relationship works correctly (patient_id + patient_type)

---

## CRITICAL POINTS

### 🔴 CRITICAL

1. **Polymorphic Relationship:** System supports Kids AND Users as patients. Use `morphTo()`, `morphMany()` and validate `patient_type` correctly.

2. **UNIQUE Authorization:** Professionals can VIEW records of ALL assigned patients, but NOT EDIT records from other professionals. Different from Kids/Checklists.

3. **Query Scope:** Don't forget to filter by professional's assigned patients (Kids via professionals pivot, Users when implemented). Data leak if forgotten!

4. **Date Format:** Input/display in d/m/Y (BR), storage in Y-m-d (ISO). Mutator must accept both.

5. **Cascade Delete:** Since Laravel doesn't support CASCADE on morphs, implement observers in Kid and User models to delete records when patient is deleted.

6. **FK Constraints:** `created_by` RESTRICT (cannot delete user who created records).

7. **LGPD Logging:** NEVER log complete medical content. Use '[CHANGED]' and secure identifier (Kid->initials or User-ID).

8. **Conditional Validation:** `patient_id` must validate existence in correct table based on `patient_type`. Use closure validation.

9. **TODO User->Professional:** Current implementation assumes any professional can create record for any User. Adjust when User->Professional assignment system is defined.

### ⚠️ ATTENTION

- Route order: trash/restore BEFORE resource()
- Transaction in ALL write operations
- `$this->authorize()` in ALL controller methods
- jQuery Mask Plugin already available in system
- Bootstrap 5 classes - no custom CSS needed

---

## REFERENCE FROM EXISTING CODE

**Main files to follow pattern:**

1. **Controller:** `app/Http/Controllers/KidsController.php` (lines 23-501)
2. **Policy:** `app/Policies/KidPolicy.php`, `app/Policies/UserPolicy.php`
3. **Model:** `app/Models/Kid.php` (especially date handling)
4. **Migration:** `database/migrations/2022_05_30_165903_create_kid_table.php`
5. **Views:** `resources/views/kids/*.blade.php`
6. **Logger:** `app/Services/Logging/KidLogger.php`
7. **Request:** `app/Http/Requests/KidRequest.php`

---

**Complete plan ready for implementation. All patterns follow existing Maiêutica conventions.**

## Field Name Mapping (Portuguese ↔ English)

For reference during implementation:

| Portuguese | English | Database Column |
|------------|---------|-----------------|
| Prontuário | Medical Record | - |
| Data | Session Date | session_date |
| Demanda | Complaint | complaint |
| Objetivo/Técnica | Objective/Technique | objective_technique |
| Registro de Evolução | Evolution Notes | evolution_notes |
| Encaminhamento/Encerramento | Referral/Closure | referral_closure |
| Criado por | Created by | created_by |
| Atualizado por | Updated by | updated_by |
| Excluído por | Deleted by | deleted_by |

**Note:** UI labels remain in Portuguese (as per system standard), but code uses English names for better maintainability and international standards.
