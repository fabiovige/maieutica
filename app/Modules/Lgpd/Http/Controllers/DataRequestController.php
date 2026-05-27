<?php

namespace App\Modules\Lgpd\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Lgpd\Application\DTOs\CreateDataRequestDTO;
use App\Modules\Lgpd\Application\Services\DataRequestService;
use App\Modules\Lgpd\Domain\Exceptions\InvalidDataRequestTransitionException;
use App\Modules\Lgpd\Domain\ValueObjects\DataRequestStatus;
use App\Modules\Lgpd\Domain\ValueObjects\DataRequestType;
use App\Modules\Lgpd\Http\Requests\StoreDataRequestRequest;
use App\Modules\Lgpd\Http\Requests\UpdateDataRequestRequest;
use App\Modules\Lgpd\Infrastructure\Models\DataRequestModel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DataRequestController extends Controller
{
    private DataRequestService $dataRequestService;

    public function __construct(DataRequestService $dataRequestService)
    {
        $this->dataRequestService = $dataRequestService;

        $this->middleware('can:lgpd-request-list')->only(['index', 'datatable']);
        $this->middleware('can:lgpd-request-show')->only('show');
        $this->middleware('can:lgpd-request-manage')->only(['store', 'assign', 'complete']);
    }

    /**
     * Exibe a listagem de requisições de direitos com DataTable.
     */
    public function index()
    {
        $types = DataRequestType::cases();
        $statuses = DataRequestStatus::cases();

        return view('modules.lgpd.requests.index', compact('types', 'statuses'));
    }

    /**
     * Endpoint server-side para DataTable com filtros por tipo, status e prazo.
     */
    public function datatable(Request $request)
    {
        $query = DataRequestModel::with(['assignedOperator', 'createdBy']);

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filtro por prazo (deadline)
        if ($request->filled('deadline_from')) {
            $query->where('deadline_at', '>=', $request->input('deadline_from'));
        }

        if ($request->filled('deadline_to')) {
            $query->where('deadline_at', '<=', $request->input('deadline_to'));
        }

        return DataTables::of($query)
            ->addColumn('type_label', function (DataRequestModel $model) {
                return $model->type instanceof DataRequestType
                    ? $model->type->label()
                    : $model->type;
            })
            ->addColumn('status_label', function (DataRequestModel $model) {
                return $model->status instanceof DataRequestStatus
                    ? $model->status->label()
                    : $model->status;
            })
            ->addColumn('status_badge', function (DataRequestModel $model) {
                $status = $model->status instanceof DataRequestStatus
                    ? $model->status->value
                    : $model->status;

                $badges = [
                    'aberta' => 'bg-primary',
                    'em_andamento' => 'bg-warning text-dark',
                    'concluida' => 'bg-success',
                    'vencida' => 'bg-danger',
                ];

                $class = $badges[$status] ?? 'bg-secondary';
                $label = $model->status instanceof DataRequestStatus
                    ? $model->status->label()
                    : $status;

                return '<span class="badge '.$class.'">'.e($label).'</span>';
            })
            ->addColumn('operator_name', function (DataRequestModel $model) {
                return $model->assignedOperator?->name ?? '—';
            })
            ->addColumn('opened_at_formatted', function (DataRequestModel $model) {
                return $model->opened_at?->format('d/m/Y H:i') ?? '—';
            })
            ->addColumn('deadline_at_formatted', function (DataRequestModel $model) {
                return $model->deadline_at?->format('d/m/Y') ?? '—';
            })
            ->addColumn('actions', function (DataRequestModel $model) {
                $actions = '<a href="'.route('lgpd.requests.show', $model->id).'" class="btn btn-sm btn-outline-primary" title="Detalhes"><i class="bi bi-eye"></i></a>';

                return $actions;
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Exibe os detalhes de uma requisição.
     */
    public function show($id)
    {
        $dataRequest = DataRequestModel::with(['assignedOperator', 'createdBy'])->findOrFail($id);

        return view('modules.lgpd.requests.show', compact('dataRequest'));
    }

    /**
     * Cria uma nova requisição via DataRequestService.
     */
    public function store(StoreDataRequestRequest $request)
    {
        try {
            $dto = new CreateDataRequestDTO(
                type: $request->input('type'),
                requesterName: $request->input('requester_name'),
                requesterDocument: $request->input('requester_document'),
                contactMethod: $request->input('contact_method'),
                operatorId: auth()->id(),
            );

            $this->dataRequestService->create($dto);

            flash('Requisição de direito criada com sucesso.')->success();
        } catch (\InvalidArgumentException $e) {
            flash($e->getMessage())->error();
        }

        return redirect()->route('lgpd.requests.index');
    }

    /**
     * Atribui o operador autenticado à requisição.
     */
    public function assign($id)
    {
        try {
            $this->dataRequestService->assignOperator((int) $id, auth()->id());

            flash('Requisição atribuída com sucesso. Status alterado para "Em andamento".')->success();
        } catch (InvalidDataRequestTransitionException $e) {
            flash($e->getMessage())->error();
        }

        return redirect()->route('lgpd.requests.show', $id);
    }

    /**
     * Conclui uma requisição com resposta ao titular.
     */
    public function complete(UpdateDataRequestRequest $request, $id)
    {
        try {
            $this->dataRequestService->complete(
                requestId: (int) $id,
                operatorId: auth()->id(),
                response: $request->input('response'),
                retentionJustification: $request->input('retention_justification'),
            );

            flash('Requisição concluída com sucesso.')->success();
        } catch (InvalidDataRequestTransitionException $e) {
            flash($e->getMessage())->error();
        }

        return redirect()->route('lgpd.requests.show', $id);
    }
}
