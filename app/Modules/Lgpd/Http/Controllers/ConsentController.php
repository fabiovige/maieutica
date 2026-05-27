<?php

namespace App\Modules\Lgpd\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Lgpd\Application\DTOs\CreateConsentDTO;
use App\Modules\Lgpd\Application\Services\ConsentService;
use App\Modules\Lgpd\Domain\Exceptions\DuplicateActiveConsentException;
use App\Modules\Lgpd\Domain\Exceptions\InvalidLegalBasisException;
use App\Modules\Lgpd\Http\Requests\StoreConsentRequest;
use App\Modules\Lgpd\Infrastructure\Models\ConsentRecordModel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ConsentController extends Controller
{
    private ConsentService $consentService;

    public function __construct(ConsentService $consentService)
    {
        $this->consentService = $consentService;
    }

    /**
     * Exibe a listagem de consentimentos com DataTable.
     */
    public function index()
    {
        return view('modules.lgpd.consents.index');
    }

    /**
     * Endpoint server-side para DataTable de consentimentos.
     * Suporta filtros por titular, finalidade e status.
     */
    public function datatable(Request $request)
    {
        $query = ConsentRecordModel::query()
            ->with(['collectedBy', 'revokedBy']);

        // Filtro por titular (subject_id)
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        // Filtro por finalidade
        if ($request->filled('purpose')) {
            $query->where('purpose', 'like', '%'.$request->input('purpose').'%');
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return DataTables::of($query)
            ->addColumn('subject_name', function (ConsentRecordModel $consent) {
                return $consent->subject_id;
            })
            ->addColumn('legal_basis_label', function (ConsentRecordModel $consent) {
                return $consent->legal_basis?->label() ?? $consent->legal_basis;
            })
            ->addColumn('status_badge', function (ConsentRecordModel $consent) {
                $status = $consent->status?->value ?? $consent->status;
                $badgeClass = $status === 'ativo' ? 'bg-success' : 'bg-secondary';
                $label = $status === 'ativo' ? 'Ativo' : 'Revogado';

                return '<span class="badge '.$badgeClass.'">'.$label.'</span>';
            })
            ->addColumn('collected_at_formatted', function (ConsentRecordModel $consent) {
                return $consent->collected_at?->format('d/m/Y H:i');
            })
            ->addColumn('actions', function (ConsentRecordModel $consent) {
                $actions = '';

                $actions .= '<a href="'.route('lgpd.consents.show', $consent->id).'" '
                    .'class="btn btn-sm btn-outline-primary" title="Detalhes">'
                    .'<i class="bi bi-eye"></i></a> ';

                if ($consent->status?->value === 'ativo' || $consent->status === 'ativo') {
                    $actions .= '<form method="POST" action="'.route('lgpd.consents.revoke', $consent->id).'" '
                        .'style="display:inline" onsubmit="return confirm(\'Tem certeza que deseja revogar este consentimento?\')">'
                        .csrf_field()
                        .'<button type="submit" class="btn btn-sm btn-outline-danger" title="Revogar">'
                        .'<i class="bi bi-x-circle"></i></button></form>';
                }

                return $actions;
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Exibe detalhes de um consentimento.
     */
    public function show(int $id)
    {
        $consent = ConsentRecordModel::with(['collectedBy', 'revokedBy', 'legalBasisHistory'])
            ->findOrFail($id);

        return view('modules.lgpd.consents.show', compact('consent'));
    }

    /**
     * Cria um novo consentimento via ConsentService.
     */
    public function store(StoreConsentRequest $request)
    {
        try {
            $dto = new CreateConsentDTO(
                subjectId: $request->validated('subject_id'),
                subjectType: $request->validated('subject_type'),
                purpose: $request->validated('purpose'),
                legalBasis: $request->validated('legal_basis'),
                termVersion: $request->validated('term_version'),
                operatorId: auth()->id(),
            );

            $this->consentService->create($dto);

            flash()->success('Consentimento registrado com sucesso.');
        } catch (DuplicateActiveConsentException $e) {
            flash()->error($e->getMessage());
        } catch (InvalidLegalBasisException $e) {
            flash()->error($e->getMessage());
        }

        return redirect()->back();
    }

    /**
     * Revoga um consentimento via ConsentService.
     */
    public function revoke(int $id)
    {
        try {
            $this->consentService->revoke($id, auth()->id());

            flash()->success('Consentimento revogado com sucesso.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            flash()->error('Consentimento não encontrado.');
        } catch (\Throwable $e) {
            flash()->error('Erro ao revogar consentimento: '.$e->getMessage());
        }

        return redirect()->back();
    }
}
