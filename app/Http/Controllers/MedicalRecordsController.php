<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicalRecordRequest;
use App\Models\Kid;
use App\Models\MedicalRecord;
use App\Models\User;
use App\Services\Logging\MedicalRecordLogger;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicalRecordsController extends Controller
{
    const PAGINATION_DEFAULT = 15;
    const MSG_CREATE_SUCCESS = 'Prontuário criado com sucesso.';
    const MSG_CREATE_ERROR = 'Erro ao criar prontuário.';
    const MSG_VERSION_SUCCESS = 'Nova versão do prontuário criada com sucesso.';
    const MSG_VERSION_ERROR = 'Erro ao criar nova versão do prontuário.';
    const MSG_DELETE_SUCCESS = 'Prontuário movido para a lixeira com sucesso.';
    const MSG_DELETE_ERROR = 'Erro ao mover prontuário para a lixeira.';
    const MSG_RESTORE_SUCCESS = 'Prontuário restaurado com sucesso.';
    const MSG_RESTORE_ERROR = 'Erro ao restaurar prontuário.';

    protected $medicalRecordLogger;

    public function __construct(MedicalRecordLogger $medicalRecordLogger)
    {
        $this->medicalRecordLogger = $medicalRecordLogger;
    }

    /**
     * Display a listing of medical records.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', MedicalRecord::class);

        $query = MedicalRecord::with(['patient', 'creator.professional']);

        // Filter by professional (admin only)
        if ($request->filled('professional_id') && auth()->user()->can('medical-record-list-all')) {
            $query->whereHas('creator.professional', function($q) use ($request) {
                $q->where('professionals.id', $request->professional_id);
            });
        }

        // Filter by patient type
        if ($request->filled('patient_type')) {
            $query->where('patient_type', $request->patient_type);
        }

        // Filter by specific patient
        if ($request->filled('patient_id') && $request->filled('patient_type')) {
            $query->where('patient_id', $request->patient_id)
                  ->where('patient_type', $request->patient_type);
        }

        // Filter by date range
        if ($request->filled('date_start')) {
            $dateStart = \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_start)->format('Y-m-d');
            $query->whereDate('session_date', '>=', $dateStart);
        }

        if ($request->filled('date_end')) {
            $dateEnd = \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_end)->format('Y-m-d');
            $query->whereDate('session_date', '<=', $dateEnd);
        }

        // Search in complaint or evolution notes
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('complaint', 'like', '%' . $search . '%')
                  ->orWhere('evolution_notes', 'like', '%' . $search . '%');
            });
        }

        // Apply scope based on user role
        // Order matters: check admin first, then professional, then patient
        $user = auth()->user();

        if ($user->can('medical-record-list-all')) {
            // Admin: sees all records (no scope applied)
            // Do nothing - query shows all
        } elseif ($user->can('medical-record-view-own')) {
            // Patient: only see their own records
            $query->forAuthPatient();
        } else {
            // Professional: only see records of assigned patients
            $query->forAuthProfessional();
        }

        // Only show current versions in listing
        $query->currentVersion();

        $medicalRecords = $query->orderBy('session_date', 'desc')
                                ->paginate(self::PAGINATION_DEFAULT);

        // Log access
        $this->medicalRecordLogger->listed([
            'filters' => $request->only(['professional_id', 'patient_type', 'patient_id', 'date_start', 'date_end', 'search']),
            'total_results' => $medicalRecords->total(),
        ]);

        // Get data for filters
        $professionals = $this->getProfessionalsForFilter();
        $kids = $this->getKidsForUser();
        $users = $this->getUserPatientsForUser();

        return view('medical-records.index', compact('medicalRecords', 'professionals', 'kids', 'users'));
    }

    /**
     * Show the form for creating a new medical record.
     */
    public function create()
    {
        $this->authorize('create', MedicalRecord::class);

        $kids = $this->getKidsForUser();
        $users = $this->getUserPatientsForUser();
        $professionals = $this->getProfessionalsForFilter();

        return view('medical-records.create', compact('kids', 'users', 'professionals'));
    }

    /**
     * Store a newly created medical record in storage.
     */
    public function store(MedicalRecordRequest $request)
    {
        $this->authorize('create', MedicalRecord::class);

        DB::beginTransaction();
        try {
            $data = $request->validated();

            // Determine who is creating the record
            if ($request->filled('professional_id') && auth()->user()->can('medical-record-create-all')) {
                // Admin is creating for a specific professional
                $professional = \App\Models\Professional::find($request->professional_id);
                if (!$professional) {
                    throw new \Exception('Profissional não encontrado.');
                }
                $creatorUser = $professional->user->first();
                if (!$creatorUser) {
                    throw new \Exception('Usuário do profissional não encontrado.');
                }
                $data['created_by'] = $creatorUser->id;
            } else {
                // Professional is creating for themselves
                $data['created_by'] = auth()->id();
            }

            $data['version'] = 1;
            $data['is_current_version'] = true;

            // Generate HTML content (with correct creator)
            $data['html_content'] = $this->generateHtml($data, $data['created_by']);

            $medicalRecord = MedicalRecord::create($data);

            $this->medicalRecordLogger->created($medicalRecord, [
                'source' => 'controller',
                'patient_type' => $medicalRecord->patient_type_name,
                'created_for_professional' => $request->filled('professional_id') ? 'yes' : 'no',
            ]);

            flash(self::MSG_CREATE_SUCCESS)->success();

            DB::commit();

            return redirect()->route('medical-records.index');
        } catch (Exception $e) {
            DB::rollBack();
            flash(self::MSG_CREATE_ERROR)->warning();

            $this->medicalRecordLogger->operationFailed('store', $e, [
                'request_data' => $request->safe()->only(['patient_type', 'patient_id', 'session_date', 'professional_id']),
            ]);

            return redirect()->route('medical-records.index');
        }
    }

    /**
     * Display the specified medical record.
     */
    public function show(MedicalRecord $medicalRecord)
    {
        $this->authorize('view', $medicalRecord);

        $medicalRecord->load(['patient', 'creator.professional']);

        // If patient is Kid, load additional relationships
        if ($medicalRecord->patient_type === 'App\\Models\\Kid' && $medicalRecord->patient) {
            $medicalRecord->patient->load(['responsible', 'professionals.user']);
        }

        $this->medicalRecordLogger->viewed($medicalRecord, [
            'source' => 'controller',
        ]);

        return view('medical-records.show', compact('medicalRecord'));
    }

    /**
     * Show the form for editing the medical record.
     */
    public function edit(MedicalRecord $medicalRecord)
    {
        $this->authorize('update', $medicalRecord);

        $kids = $this->getKidsForUser();
        $users = $this->getUserPatientsForUser();

        return view('medical-records.edit', compact('medicalRecord', 'kids', 'users'));
    }

    /**
     * Update the specified medical record in storage.
     */
    public function update(MedicalRecordRequest $request, MedicalRecord $medicalRecord)
    {
        $this->authorize('update', $medicalRecord);

        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['updated_by'] = auth()->id();

            // Track changes for logging (compare with original attributes, not accessors)
            $changes = [];
            foreach ($data as $key => $value) {
                $originalValue = $medicalRecord->getOriginal($key);
                if ($originalValue != $value && $key !== 'session_date') {
                    $changes[$key] = [
                        'old' => $originalValue,
                        'new' => $value,
                    ];
                }
            }

            // Regenerate HTML content (after updating to get fresh data)
            $medicalRecord->update($data);
            $medicalRecord->refresh();

            // Generate HTML with updated record
            $medicalRecord->html_content = $this->generateHtmlFromRecord($medicalRecord);
            $medicalRecord->save();

            $this->medicalRecordLogger->updated($medicalRecord, $changes, [
                'source' => 'controller',
            ]);

            flash('Prontuário atualizado com sucesso.')->success();

            DB::commit();

            return redirect()->route('medical-records.show', $medicalRecord);
        } catch (Exception $e) {
            DB::rollBack();
            flash('Erro ao atualizar prontuário.')->warning();

            $this->medicalRecordLogger->operationFailed('update', $e, [
                'medical_record_id' => $medicalRecord->id,
            ]);

            return redirect()->back()->withInput();
        }
    }

    /**
     * Show the form for creating a new version of the medical record.
     */
    public function newVersion(MedicalRecord $medicalRecord)
    {
        $this->authorize('update', $medicalRecord);

        // Only allow creating new version from current version
        if (!$medicalRecord->is_current_version) {
            flash('Não é possível criar nova versão de um prontuário antigo. Use a versão atual.')->warning();
            return redirect()->route('medical-records.show', $medicalRecord->getLatestVersion());
        }

        $kids = $this->getKidsForUser();
        $users = $this->getUserPatientsForUser();

        return view('medical-records.new-version', compact('medicalRecord', 'kids', 'users'));
    }

    /**
     * Create a new version of the medical record.
     */
    public function createNewVersion(MedicalRecordRequest $request, MedicalRecord $medicalRecord)
    {
        $this->authorize('update', $medicalRecord);

        // Only allow creating new version from current version
        if (!$medicalRecord->is_current_version) {
            flash('Não é possível criar nova versão de um prontuário antigo.')->warning();
            return redirect()->back();
        }

        DB::beginTransaction();
        try {
            // Mark current version as not current
            $medicalRecord->is_current_version = false;
            $medicalRecord->save();

            // Create new version
            $parentId = $medicalRecord->parent_id ?? $medicalRecord->id;
            $newVersion = $medicalRecord->version + 1;

            $data = $request->validated();
            $data['parent_id'] = $parentId;
            $data['version'] = $newVersion;
            $data['is_current_version'] = true;
            $data['created_by'] = auth()->id();

            // Generate HTML
            $data['html_content'] = $this->generateHtml($data);

            $newRecord = MedicalRecord::create($data);

            $this->medicalRecordLogger->versionCreated($newRecord, $medicalRecord, [
                'source' => 'controller',
            ]);

            flash(self::MSG_VERSION_SUCCESS)->success();

            DB::commit();

            return redirect()->route('medical-records.show', $newRecord);
        } catch (Exception $e) {
            DB::rollBack();
            flash(self::MSG_VERSION_ERROR)->warning();

            $this->medicalRecordLogger->operationFailed('createNewVersion', $e, [
                'medical_record_id' => $medicalRecord->id,
            ]);

            return redirect()->back();
        }
    }

    /**
     * Display version history of a medical record.
     */
    public function history(MedicalRecord $medicalRecord)
    {
        $this->authorize('view', $medicalRecord);

        $versions = $medicalRecord->getAllVersions();

        return view('medical-records.history', compact('medicalRecord', 'versions'));
    }

    /**
     * Generate PDF from HTML content.
     */
    public function generatePdf(MedicalRecord $medicalRecord)
    {
        $this->authorize('view', $medicalRecord);

        // For now, just download the HTML
        // TODO: Integrate with PDF library (dompdf, wkhtmltopdf, etc.)
        $filename = 'prontuario_' . $medicalRecord->id . '_v' . $medicalRecord->version . '.html';

        return response($medicalRecord->html_content)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Generate HTML content for medical record.
     */
    private function generateHtml(array $data, $creatorId = null)
    {
        // Get patient name
        if (isset($data['patient_type']) && isset($data['patient_id'])) {
            $patientModel = $data['patient_type'];
            $patient = $patientModel::find($data['patient_id']);
            $data['patient_name'] = $patient ? $patient->name : 'N/D';
        }

        // Get creator info (use provided creatorId or auth user)
        $creator = User::find($creatorId ?? auth()->id());
        $data['creator_name'] = $creator ? $creator->name : 'N/D';

        // Prepare assets (watermark and logo)
        $watermark = base64_encode(file_get_contents(public_path('images/bg-doc.png')));
        $logo = base64_encode(file_get_contents(public_path('images/logotipo.png')));

        // Get professional info for signature
        $professional = $creator ? $creator->professional->first() : null;
        $nomePsicologo = $creator ? strtoupper($creator->name) : 'PROFISSIONAL';
        $crp = $professional->registration_number ?? '';
        $cidade = $creator->city ?? 'Santana de Parnaíba';

        return view('medical-records.pdf-template', [
            'record' => (object) $data,
            'watermark' => $watermark,
            'logo' => $logo,
            'nome_psicologo' => $nomePsicologo,
            'crp' => $crp,
            'cidade' => $cidade,
            'data_formatada' => date('d/m/Y'),
        ])->render();
    }

    /**
     * Generate HTML content from MedicalRecord object (for updates).
     */
    private function generateHtmlFromRecord(MedicalRecord $medicalRecord)
    {
        // Load relationships
        $medicalRecord->load(['patient', 'creator']);
        if ($medicalRecord->creator) {
            $medicalRecord->creator->load('professional');
        }

        // Prepare assets (watermark and logo)
        $watermark = base64_encode(file_get_contents(public_path('images/bg-doc.png')));
        $logo = base64_encode(file_get_contents(public_path('images/logotipo.png')));

        // Get professional info
        $creator = $medicalRecord->creator;
        $professional = $creator ? $creator->professional->first() : null;
        $nomePsicologo = $creator ? strtoupper($creator->name) : 'PROFISSIONAL';
        $crp = $professional->registration_number ?? '';
        $cidade = $creator->city ?? 'Santana de Parnaíba';

        return view('medical-records.pdf-template', [
            'record' => $medicalRecord,
            'watermark' => $watermark,
            'logo' => $logo,
            'nome_psicologo' => $nomePsicologo,
            'crp' => $crp,
            'cidade' => $cidade,
            'data_formatada' => date('d/m/Y'),
        ])->render();
    }

    /**
     * Remove the specified medical record from storage (soft delete).
     */
    public function destroy(MedicalRecord $medicalRecord)
    {
        $this->authorize('delete', $medicalRecord);

        DB::beginTransaction();
        try {
            $medicalRecord->deleted_by = auth()->id();
            $medicalRecord->save();

            $medicalRecord->delete();

            $this->medicalRecordLogger->deleted($medicalRecord, [
                'source' => 'controller',
            ]);

            flash(self::MSG_DELETE_SUCCESS)->success();

            DB::commit();

            return redirect()->route('medical-records.index');
        } catch (Exception $e) {
            DB::rollBack();
            flash(self::MSG_DELETE_ERROR)->warning();

            $this->medicalRecordLogger->operationFailed('destroy', $e, [
                'medical_record_id' => $medicalRecord->id,
            ]);

            return redirect()->back();
        }
    }

    /**
     * Display a listing of trashed medical records.
     */
    public function trash()
    {
        $this->authorize('viewTrash', MedicalRecord::class);

        $medicalRecords = MedicalRecord::onlyTrashed()
            ->with(['patient', 'creator', 'deleter'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(self::PAGINATION_DEFAULT);

        $this->medicalRecordLogger->trashViewed([
            'total_trashed' => $medicalRecords->total(),
        ]);

        return view('medical-records.trash', compact('medicalRecords'));
    }

    /**
     * Restore a trashed medical record.
     */
    public function restore($id)
    {
        $medicalRecord = MedicalRecord::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $medicalRecord);

        DB::beginTransaction();
        try {
            $medicalRecord->restore();

            $this->medicalRecordLogger->restored($medicalRecord, [
                'source' => 'controller',
            ]);

            flash(self::MSG_RESTORE_SUCCESS)->success();

            DB::commit();

            return redirect()->route('medical-records.index');
        } catch (Exception $e) {
            DB::rollBack();
            flash(self::MSG_RESTORE_ERROR)->warning();

            $this->medicalRecordLogger->operationFailed('restore', $e, [
                'medical_record_id' => $id,
            ]);

            return redirect()->back();
        }
    }

    /**
     * Download PDF of medical record.
     */
    public function downloadPdf(MedicalRecord $medicalRecord)
    {
        $this->authorize('view', $medicalRecord);

        // Load relationships
        $medicalRecord->load(['patient', 'creator']);

        // Get professional info for signature
        if ($medicalRecord->creator) {
            $medicalRecord->creator->load('professional');
        }

        // Prepare assets (watermark and logo)
        $watermark = base64_encode(file_get_contents(public_path('images/bg-doc.png')));
        $logo = base64_encode(file_get_contents(public_path('images/logotipo.png')));

        // Get professional info
        $creator = $medicalRecord->creator;
        $professional = $creator ? $creator->professional->first() : null;
        $nomePsicologo = $creator ? strtoupper($creator->name) : 'PROFISSIONAL';
        $crp = $professional->registration_number ?? '';
        $cidade = $creator->city ?? 'Santana de Parnaíba';

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('medical-records.pdf-template', [
            'record' => $medicalRecord,
            'watermark' => $watermark,
            'logo' => $logo,
            'nome_psicologo' => $nomePsicologo,
            'crp' => $crp,
            'cidade' => $cidade,
            'data_formatada' => date('d/m/Y'),
        ]);

        $pdf->setPaper('A4', 'portrait');

        // Log download
        $this->medicalRecordLogger->viewed($medicalRecord, [
            'source' => 'pdf_download',
        ]);

        // Generate filename
        $patientName = $medicalRecord->patient_name ?? 'Paciente';
        $patientName = preg_replace('/[^A-Za-z0-9\-]/', '_', $patientName);
        $date = $medicalRecord->session_date ?? date('d-m-Y');
        $date = str_replace('/', '-', $date);
        $filename = "prontuario_{$patientName}_{$date}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Get Kids for current user based on permissions.
     */
    private function getKidsForUser()
    {
        if (auth()->user()->can('medical-record-list-all')) {
            return Kid::orderBy('name')->get();
        }

        $professional = auth()->user()->professional->first();
        if (!$professional) {
            return collect([]);
        }

        return Kid::whereHas('professionals', function ($q) use ($professional) {
            $q->where('professional_id', $professional->id);
        })->orderBy('name')->get();
    }

    /**
     * Get User patients for current user based on permissions.
     */
    private function getUserPatientsForUser()
    {
        if (auth()->user()->can('medical-record-list-all')) {
            // Admin sees all active users
            return User::where('allow', 1)->orderBy('name')->get();
        }

        // Professional sees only their assigned user patients
        $professional = auth()->user()->professional->first();

        if (!$professional) {
            return collect([]);
        }

        return $professional->patients()->orderBy('name')->get();
    }

    /**
     * Get professionals list for filter (admin only).
     */
    private function getProfessionalsForFilter()
    {
        if (!auth()->user()->can('medical-record-list-all')) {
            return collect([]);
        }

        return \App\Models\Professional::with('user')
            ->whereHas('user', function($q) {
                $q->where('allow', 1); // Only active users
            })
            ->orderBy('id')
            ->get();
    }
}
