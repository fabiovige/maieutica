<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfessionalRequest;
use App\Models\Professional;
use App\Models\Specialty;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use App\Services\Logging\ProfessionalLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProfessionalController extends Controller
{
    protected $professionalLogger;

    public function __construct(ProfessionalLogger $professionalLogger)
    {
        $this->professionalLogger = $professionalLogger;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Professional::class);

        $query = Professional::with(['user', 'specialty'])
            ->withCount(['kids' => function ($query) {
                $query->whereNull('kids.deleted_at');
            }]);

        // Filtrar por tipo de usuário
        $user = auth()->user();

        // Admin vê todos os profissionais
        if (!$user->can('professional-list-all')) {
            // Profissional vê apenas seus próprios dados
            $userProfessional = $user->professional->first();
            if ($userProfessional) {
                $query->where('id', $userProfessional->id);
            } else {
                // Se não é profissional e não tem permissão -all, não vê nada
                $query->whereRaw('1 = 0');
            }
        }

        // Filtro de busca geral (nome do usuário, especialidade, registro)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('registration_number', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('specialty', function($specialtyQuery) use ($search) {
                      $specialtyQuery->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        $professionals = $query->orderBy('created_at', 'desc')->paginate(self::PAGINATION_DEFAULT);

        $this->professionalLogger->listed([
            'search' => $request->input('search'),
            'total_results' => $professionals->total(),
        ]);

        return view('professionals.index', compact('professionals'));
    }

    public function show(Professional $professional)
    {
        $this->authorize('view', $professional);

        $professional->load(['user', 'specialty', 'kids']);

        $this->professionalLogger->viewed($professional, 'details');

        return view('professionals.show', compact('professional'));
    }

    public function edit(Professional $professional)
    {
        $this->authorize('update', $professional);

        try {
            $specialties = Specialty::orderBy('name')->get();

            $this->professionalLogger->viewed($professional, 'edit');

            return view('professionals.edit', compact('professional', 'specialties'));
        } catch (\Exception $e) {
            $this->professionalLogger->operationFailed('edit', $e, [
                'professional_id' => $professional->id,
            ]);

            flash('Erro ao carregar dados do profissional.')->error();

            return redirect()->route('professionals.index');
        }
    }

    public function create()
    {
        $this->authorize('create', Professional::class);

        try {
            $specialties = Specialty::orderBy('name')->get();

            return view('professionals.create', compact('specialties'));
        } catch (\Exception $e) {
            $this->professionalLogger->operationFailed('create', $e);

            flash('Erro ao carregar formulário de criação.')->error();

            return redirect()->route('professionals.index');
        }
    }

    public function store(ProfessionalRequest $request)
    {
        $this->authorize('create', Professional::class);

        DB::beginTransaction();
        try {
            $validated = $request->validated();

            // Gerar senha temporária
            $temporaryPassword = Str::random(10);

            // Criar o usuário
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => bcrypt($temporaryPassword),
                'allow' => $request->has('allow'),
                'created_by' => auth()->id(),
            ]);

            // Atribuir role 'profissional' (para agrupar permissions automaticamente)
            // IMPORTANTE: O código usa APENAS can() para verificações, nunca hasRole()
            if (\App\Models\Role::where('name', 'profissional')->exists()) {
                $user->assignRole('profissional');
            } else {
                $this->professionalLogger->roleMissing($user->id, 'profissional', [
                    'email' => $user->email,
                ]);
            }

            // Criar o profissional
            $professional = Professional::create([
                'specialty_id' => $validated['specialty_id'],
                'registration_number' => $validated['registration_number'],
                'bio' => $validated['bio'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Vincular usuário ao profissional
            $professional->user()->attach($user->id);

            // Log user linking
            $this->professionalLogger->userLinked($professional, $user->id, [
                'source' => 'controller',
                'on_creation' => true,
            ]);

            // Observer will log at model level
            $this->professionalLogger->created($professional, [
                'source' => 'controller',
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);

            DB::commit();

            flash('Profissional criado com sucesso.')->success();

            return redirect()->route('professionals.index');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->professionalLogger->operationFailed('store', $e);

            flash('Erro ao criar profissional: ' . $e->getMessage())->error();

            return redirect()->back()->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $professional = Professional::with('user')->findOrFail($id);
        $this->authorize('update', $professional);

        try {
            $user = $professional->user->first();

            if (!$user) {
                throw new \Exception('Usuário não encontrado');
            }

            // Validação
            $request->validate([
                'name' => 'required',
                'email' => 'required|email',
                'phone' => 'required',
                'specialty_id' => 'required',
                'registration_number' => 'required',
            ], [
                'phone.required' => 'O campo telefone é obrigatório.'
            ]);

            DB::beginTransaction();

            // Get original data for change tracking
            $originalProfessionalData = $professional->only(['specialty_id', 'registration_number', 'bio']);
            $originalUserData = $user->only(['name', 'email', 'phone', 'allow']);

            // Atualizar dados do usuário
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'allow' => $request->has('allow'),
                'updated_by' => auth()->id()
            ]);

            // Atualizar dados do profissional
            $professional->update([
                'specialty_id' => $request->specialty_id,
                'registration_number' => $request->registration_number,
                'bio' => $request->bio,
                'updated_by' => auth()->id()
            ]);

            // Track what changed in professional
            $changes = [];
            $newProfessionalData = $professional->only(['specialty_id', 'registration_number', 'bio']);
            foreach ($newProfessionalData as $key => $value) {
                if ($originalProfessionalData[$key] != $value) {
                    $changes[$key] = ['old' => $originalProfessionalData[$key], 'new' => $value];
                }
            }

            // Track what changed in user
            $userChanges = [];
            $newUserData = $user->only(['name', 'email', 'phone', 'allow']);
            foreach ($newUserData as $key => $value) {
                if ($originalUserData[$key] != $value) {
                    $userChanges[$key] = ['old' => $originalUserData[$key], 'new' => $value];
                }
            }

            // Observer will log at model level
            if (!empty($changes)) {
                $this->professionalLogger->updated($professional, $changes, [
                    'source' => 'controller',
                    'user_also_updated' => !empty($userChanges),
                    'user_changes' => $userChanges,
                ]);
            }

            DB::commit();

            flash('Profissional atualizado com sucesso!')->success();
            return redirect()->route('professionals.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->professionalLogger->operationFailed('update', $e, [
                'professional_id' => $id,
            ]);

            flash('Erro ao atualizar o profissional')->warning();
            return redirect()->back()->withInput();
        }
    }

    public function destroy(Professional $professional)
    {
        $this->authorize('delete', $professional);

        DB::beginTransaction();

        try {
            // Verifica se o profissional tem kids vinculados
            $kidsCount = $professional->kids()->count();
            if ($kidsCount > 0) {
                $this->professionalLogger->accessDenied('delete', $professional, [
                    'reason' => 'Profissional tem crianças vinculadas',
                    'kids_count' => $kidsCount,
                ]);

                throw new \Exception('Não é possível mover para lixeira, pois existem crianças vinculadas a este profissional.');
            }

            // Move para lixeira (soft delete)
            $professional->delete();

            // Observer will log at model level
            $this->professionalLogger->deleted($professional, [
                'source' => 'controller',
                'user_id' => $professional->user->first()->id ?? null,
            ]);

            DB::commit();
            flash('Profissional movido para a lixeira com sucesso.')->success();

            return redirect()->route('professionals.index');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->professionalLogger->operationFailed('destroy', $e, [
                'professional_id' => $professional->id,
            ]);

            flash($e->getMessage())->error();

            return redirect()->back();
        }
    }

    public function trash()
    {
        $this->authorize('viewAny', Professional::class);

        $professionals = Professional::onlyTrashed()
            ->with(['user', 'specialty'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(self::PAGINATION_DEFAULT);

        $this->professionalLogger->trashViewed([
            'total_trashed' => $professionals->total(),
        ]);

        return view('professionals.trash', compact('professionals'));
    }

    public function restore($id)
    {
        DB::beginTransaction();
        try {
            $professional = Professional::onlyTrashed()->findOrFail($id);

            $this->authorize('update', $professional);

            // Restaura o profissional da lixeira
            $professional->restore();

            // Observer will log at model level
            $this->professionalLogger->restored($professional, [
                'source' => 'controller',
                'user_id' => $professional->user->first()->id ?? null,
            ]);

            DB::commit();

            flash('Profissional restaurado com sucesso.')->success();

            return redirect()->route('professionals.trash');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->professionalLogger->operationFailed('restore', $e, [
                'professional_id' => $id,
            ]);

            flash('Erro ao restaurar profissional: ' . $e->getMessage())->warning();

            return redirect()->back();
        }
    }

    public function deactivate(Professional $professional)
    {
        $this->authorize('update', $professional);

        try {
            DB::beginTransaction();

            $user = $professional->user->first();
            if (! $user) {
                throw new \Exception('Usuário não encontrado');
            }

            // Desativa o user vinculado
            $user->update([
                'allow' => false,
                'updated_by' => auth()->id(),
            ]);

            $this->professionalLogger->deactivated($professional, [
                'source' => 'controller',
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);

            DB::commit();

            flash('Profissional desativado com sucesso. O usuário vinculado também foi desativado.')->success();

            return redirect()->route('professionals.index');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->professionalLogger->operationFailed('deactivate', $e, [
                'professional_id' => $professional->id,
            ]);

            flash('Erro ao desativar profissional.')->error();

            return redirect()->back();
        }
    }

    public function activate(Professional $professional)
    {
        $this->authorize('update', $professional);

        try {
            DB::beginTransaction();

            $user = $professional->user->first();
            if (! $user) {
                throw new \Exception('Usuário não encontrado');
            }

            // Ativa o user vinculado
            $user->update([
                'allow' => true,
                'updated_by' => auth()->id(),
            ]);

            $this->professionalLogger->activated($professional, [
                'source' => 'controller',
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);

            DB::commit();

            flash('Profissional ativado com sucesso. O usuário vinculado também foi ativado.')->success();

            return redirect()->route('professionals.index');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->professionalLogger->operationFailed('activate', $e, [
                'professional_id' => $professional->id,
            ]);

            flash('Erro ao ativar profissional.')->error();

            return redirect()->back();
        }
    }

    /**
     * Show form to assign user patients to a professional
     */
    public function assignPatientsForm(Professional $professional)
    {
        $this->authorize('update', $professional);

        // Get all active users (excluding the professional's own user)
        $professionalUserId = $professional->user->first()?->id;

        $availablePatients = User::where('allow', 1)
            ->where('id', '!=', $professionalUserId)
            ->orderBy('name')
            ->get();

        // Get currently assigned patients
        $assignedPatientIds = $professional->patients()->pluck('users.id')->toArray();

        $this->professionalLogger->viewed($professional, 'assign_patients_form');

        return view('professionals.assign-patients', compact('professional', 'availablePatients', 'assignedPatientIds'));
    }

    /**
     * Sync assigned user patients for a professional
     */
    public function syncPatients(Request $request, Professional $professional)
    {
        $this->authorize('update', $professional);

        try {
            DB::beginTransaction();

            $patients = $request->input('patients', []);

            // Get original assignments for logging
            $originalPatients = $professional->patients()->pluck('users.id')->toArray();

            // Sync patients with timestamps
            $syncData = [];
            foreach ($patients as $patientId) {
                $syncData[$patientId] = [
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $professional->patients()->sync($syncData);

            // Log changes
            $attached = array_diff($patients, $originalPatients);
            $detached = array_diff($originalPatients, $patients);

            foreach ($attached as $patientId) {
                $this->professionalLogger->created($professional, [
                    'action' => 'patient_attached',
                    'patient_id' => $patientId,
                    'patient_type' => 'User',
                ]);
            }

            foreach ($detached as $patientId) {
                $this->professionalLogger->deleted($professional, [
                    'action' => 'patient_detached',
                    'patient_id' => $patientId,
                    'patient_type' => 'User',
                ]);
            }

            DB::commit();

            flash('Pacientes atribuídos com sucesso!')->success();

            return redirect()->route('professionals.show', $professional);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->professionalLogger->operationFailed('sync_patients', $e, [
                'professional_id' => $professional->id,
            ]);

            flash('Erro ao atribuir pacientes.')->error();

            return redirect()->back();
        }
    }
}
