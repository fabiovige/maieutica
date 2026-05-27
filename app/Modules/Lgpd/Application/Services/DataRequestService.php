<?php

namespace App\Modules\Lgpd\Application\Services;

use App\Modules\Lgpd\Application\DTOs\CreateDataRequestDTO;
use App\Modules\Lgpd\Domain\Events\DataDeletionCompleted;
use App\Modules\Lgpd\Domain\Exceptions\InvalidDataRequestTransitionException;
use App\Modules\Lgpd\Domain\ValueObjects\DataRequestStatus;
use App\Modules\Lgpd\Domain\ValueObjects\DataRequestType;
use App\Modules\Lgpd\Infrastructure\Models\DataRequestModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class DataRequestService
{
    private BusinessDayCalculator $businessDayCalculator;

    public function __construct(BusinessDayCalculator $businessDayCalculator)
    {
        $this->businessDayCalculator = $businessDayCalculator;
    }

    /**
     * Cria uma nova DataRequest com status "aberta" e prazo de 15 dias úteis.
     *
     * @throws \InvalidArgumentException se campos obrigatórios estiverem ausentes
     */
    public function create(CreateDataRequestDTO $dto): DataRequestModel
    {
        $this->validateRequiredFields($dto);

        $now = Carbon::now();
        $deadline = $this->businessDayCalculator->addBusinessDays($now->copy(), 15);

        return DataRequestModel::create([
            'type' => $dto->type,
            'requester_name' => $dto->requesterName,
            'requester_document' => $dto->requesterDocument,
            'contact_method' => $dto->contactMethod,
            'status' => DataRequestStatus::ABERTA->value,
            'opened_at' => $now,
            'deadline_at' => $deadline,
            'created_by' => $dto->operatorId,
        ]);
    }

    /**
     * Atribui um operador à requisição, transitando de aberta → em_andamento.
     *
     * @throws InvalidDataRequestTransitionException se a transição não for permitida
     */
    public function assignOperator(int $requestId, int $operatorId): DataRequestModel
    {
        $request = DataRequestModel::findOrFail($requestId);

        $this->validateTransition($request, DataRequestStatus::EM_ANDAMENTO);

        $request->update([
            'status' => DataRequestStatus::EM_ANDAMENTO->value,
            'assigned_operator_id' => $operatorId,
            'started_at' => Carbon::now(),
        ]);

        return $request->fresh();
    }

    /**
     * Conclui uma requisição, transitando de em_andamento → concluída.
     * Se o tipo for "eliminacao", dispara evento DataDeletionCompleted.
     *
     * @throws InvalidDataRequestTransitionException se a transição não for permitida
     */
    public function complete(
        int $requestId,
        int $operatorId,
        string $response,
        ?string $retentionJustification = null
    ): DataRequestModel {
        $request = DataRequestModel::findOrFail($requestId);

        $this->validateTransition($request, DataRequestStatus::CONCLUIDA);

        $request->update([
            'status' => DataRequestStatus::CONCLUIDA->value,
            'assigned_operator_id' => $operatorId,
            'completed_at' => Carbon::now(),
            'response' => mb_substr($response, 0, 5000),
            'retention_justification' => $retentionJustification
                ? mb_substr($retentionJustification, 0, 2000)
                : null,
        ]);

        $request = $request->fresh();

        // Se for eliminação, disparar evento DataDeletionCompleted
        if ($request->type === DataRequestType::ELIMINACAO) {
            $this->safeDispatch(new DataDeletionCompleted(
                dataRequestId: $request->id,
                subjectId: $request->created_by,
                deletedCategories: ['dados_cadastrais'],
            ));
        }

        return $request;
    }

    /**
     * Marca uma requisição como vencida (apenas de aberta ou em_andamento).
     *
     * @throws InvalidDataRequestTransitionException se a transição não for permitida
     */
    public function markAsExpired(int $requestId): DataRequestModel
    {
        $request = DataRequestModel::findOrFail($requestId);

        $this->validateTransition($request, DataRequestStatus::VENCIDA);

        $request->update([
            'status' => DataRequestStatus::VENCIDA->value,
        ]);

        return $request->fresh();
    }

    /**
     * Lista requisições com filtros opcionais por tipo, status e prazo.
     *
     * Filtros suportados:
     * - type: string (valor do enum DataRequestType)
     * - status: string (valor do enum DataRequestStatus)
     * - deadline_before: Carbon|string (requisições com prazo antes desta data)
     * - deadline_after: Carbon|string (requisições com prazo após esta data)
     */
    public function listFiltered(array $filters): Collection
    {
        $query = DataRequestModel::query();

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['deadline_before'])) {
            $deadline = $filters['deadline_before'] instanceof Carbon
                ? $filters['deadline_before']
                : Carbon::parse($filters['deadline_before']);
            $query->where('deadline_at', '<=', $deadline);
        }

        if (! empty($filters['deadline_after'])) {
            $deadline = $filters['deadline_after'] instanceof Carbon
                ? $filters['deadline_after']
                : Carbon::parse($filters['deadline_after']);
            $query->where('deadline_at', '>=', $deadline);
        }

        return $query->orderBy('deadline_at', 'asc')->get();
    }

    /**
     * Valida campos obrigatórios do DTO de criação.
     *
     * @throws \InvalidArgumentException
     */
    private function validateRequiredFields(CreateDataRequestDTO $dto): void
    {
        $missing = [];

        if (empty($dto->type)) {
            $missing[] = 'tipo';
        }

        if (empty($dto->requesterDocument)) {
            $missing[] = 'documento do solicitante (CPF)';
        }

        if (empty($dto->contactMethod)) {
            $missing[] = 'meio de contato';
        }

        if (empty($dto->requesterName)) {
            $missing[] = 'nome do solicitante';
        }

        if (! empty($missing)) {
            throw new \InvalidArgumentException(
                'Campos obrigatórios ausentes: '.implode(', ', $missing).'.'
            );
        }

        // Validar que o tipo é válido
        if (! DataRequestType::tryFrom($dto->type)) {
            throw new \InvalidArgumentException(
                "Tipo de requisição inválido: '{$dto->type}'. Tipos válidos: "
                .implode(', ', array_column(DataRequestType::cases(), 'value'))
            );
        }
    }

    /**
     * Valida se a transição de estado é permitida pela máquina de estados.
     *
     * Transições válidas:
     * - aberta → em_andamento
     * - em_andamento → concluida
     * - {aberta, em_andamento} → vencida
     *
     * @throws InvalidDataRequestTransitionException
     */
    private function validateTransition(DataRequestModel $request, DataRequestStatus $targetStatus): void
    {
        $currentStatus = $request->status;

        $allowedTransitions = [
            DataRequestStatus::ABERTA->value => [
                DataRequestStatus::EM_ANDAMENTO->value,
                DataRequestStatus::VENCIDA->value,
            ],
            DataRequestStatus::EM_ANDAMENTO->value => [
                DataRequestStatus::CONCLUIDA->value,
                DataRequestStatus::VENCIDA->value,
            ],
        ];

        $currentValue = $currentStatus instanceof DataRequestStatus
            ? $currentStatus->value
            : $currentStatus;

        $allowed = $allowedTransitions[$currentValue] ?? [];

        if (! in_array($targetStatus->value, $allowed, true)) {
            throw InvalidDataRequestTransitionException::forTransition(
                $currentValue,
                $targetStatus->value
            );
        }
    }

    /**
     * Dispara evento de forma segura, sem interromper a operação principal.
     */
    private function safeDispatch(object $event): void
    {
        try {
            event($event);
        } catch (\Throwable $e) {
            Log::error('[LGPD] Event dispatch failed', [
                'event' => get_class($event),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
