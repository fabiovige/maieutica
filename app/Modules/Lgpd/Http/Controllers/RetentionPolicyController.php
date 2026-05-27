<?php

namespace App\Modules\Lgpd\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Lgpd\Application\DTOs\CreateRetentionPolicyDTO;
use App\Modules\Lgpd\Application\Services\RetentionPolicyService;
use App\Modules\Lgpd\Domain\Exceptions\RetentionPeriodViolationException;
use App\Modules\Lgpd\Http\Requests\StoreRetentionPolicyRequest;
use App\Modules\Lgpd\Infrastructure\Models\RetentionPolicyModel;

class RetentionPolicyController extends Controller
{
    private RetentionPolicyService $retentionPolicyService;

    public function __construct(RetentionPolicyService $retentionPolicyService)
    {
        $this->retentionPolicyService = $retentionPolicyService;

        $this->middleware('can:lgpd-retention-list')->only('index');
        $this->middleware('can:lgpd-retention-manage')->only(['store', 'update']);
    }

    /**
     * Exibe a listagem de políticas de retenção.
     */
    public function index()
    {
        $policies = RetentionPolicyModel::with(['createdBy', 'updatedBy'])
            ->orderBy('category')
            ->get();

        return view('modules.lgpd.retention.index', compact('policies'));
    }

    /**
     * Cria uma nova política de retenção.
     */
    public function store(StoreRetentionPolicyRequest $request)
    {
        try {
            $dto = new CreateRetentionPolicyDTO(
                category: $request->input('category'),
                retentionDays: (int) $request->input('retention_days'),
                expirationAction: $request->input('expiration_action'),
                operatorId: auth()->id(),
            );

            $this->retentionPolicyService->create($dto);

            flash('Política de retenção criada com sucesso.')->success();
        } catch (RetentionPeriodViolationException $e) {
            flash($e->getMessage())->error();
        }

        return redirect()->route('lgpd.retention.index');
    }

    /**
     * Atualiza uma política de retenção existente.
     */
    public function update(StoreRetentionPolicyRequest $request, $id)
    {
        try {
            $data = [
                'category' => $request->input('category'),
                'retention_days' => (int) $request->input('retention_days'),
                'expiration_action' => $request->input('expiration_action'),
                'updated_by' => auth()->id(),
            ];

            $this->retentionPolicyService->update($id, $data);

            flash('Política de retenção atualizada com sucesso.')->success();
        } catch (RetentionPeriodViolationException $e) {
            flash($e->getMessage())->error();
        }

        return redirect()->route('lgpd.retention.index');
    }
}
