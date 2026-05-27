<?php

namespace App\Modules\Lgpd\Application\Services;

use App\Modules\Lgpd\Application\DTOs\CreateRetentionPolicyDTO;
use App\Modules\Lgpd\Domain\Exceptions\RetentionPeriodViolationException;
use App\Modules\Lgpd\Domain\ValueObjects\DataCategory;
use App\Modules\Lgpd\Infrastructure\Models\RetentionPolicyModel;

class RetentionPolicyService
{
    /**
     * Cria uma nova política de retenção.
     *
     * Valida que o período de retenção não é inferior ao mínimo legal
     * da categoria e cria o registro com o campo legal_minimum_days preenchido.
     *
     * @throws RetentionPeriodViolationException
     */
    public function create(CreateRetentionPolicyDTO $dto): RetentionPolicyModel
    {
        $this->validateAgainstLegalMinimum($dto->category, $dto->retentionDays);

        $minimumDays = $this->getMinimumRetentionDays($dto->category);

        return RetentionPolicyModel::create([
            'category' => $dto->category,
            'retention_days' => $dto->retentionDays,
            'expiration_action' => $dto->expirationAction,
            'legal_minimum_days' => $minimumDays,
            'legal_reference' => $this->getLegalReference($dto->category),
            'created_by' => $dto->operatorId,
        ]);
    }

    /**
     * Atualiza uma política de retenção existente.
     *
     * Se retention_days está sendo atualizado, valida contra o mínimo legal.
     *
     * @throws RetentionPeriodViolationException
     */
    public function update(int $policyId, array $data): RetentionPolicyModel
    {
        $policy = RetentionPolicyModel::findOrFail($policyId);

        // Se está atualizando retention_days, validar contra mínimo legal
        if (isset($data['retention_days'])) {
            $category = $data['category'] ?? $policy->getRawOriginal('category') ?? $policy->category;

            // Se category é um enum, extrair o valor string
            if ($category instanceof DataCategory) {
                $category = $category->value;
            }

            $this->validateAgainstLegalMinimum($category, (int) $data['retention_days']);

            // Atualizar legal_minimum_days caso a categoria tenha mudado
            $data['legal_minimum_days'] = $this->getMinimumRetentionDays($category);
        }

        // Se está atualizando a categoria sem atualizar retention_days, validar também
        if (isset($data['category']) && ! isset($data['retention_days'])) {
            $category = $data['category'];

            if ($category instanceof DataCategory) {
                $category = $category->value;
            }

            $currentRetentionDays = $policy->retention_days;
            $this->validateAgainstLegalMinimum($category, $currentRetentionDays);

            $data['legal_minimum_days'] = $this->getMinimumRetentionDays($category);
            $data['legal_reference'] = $this->getLegalReference($category);
        }

        $policy->update($data);

        return $policy->fresh();
    }

    /**
     * Valida se o período de retenção atende ao mínimo legal da categoria.
     *
     * @throws RetentionPeriodViolationException quando período < mínimo legal
     */
    public function validateAgainstLegalMinimum(string $category, int $retentionDays): bool
    {
        $minimumDays = $this->getMinimumRetentionDays($category);

        if ($retentionDays < $minimumDays) {
            throw RetentionPeriodViolationException::forCategory($category, $retentionDays, $minimumDays);
        }

        return true;
    }

    /**
     * Retorna o mínimo legal de retenção em dias para a categoria informada.
     *
     * Valores baseados em legislação brasileira:
     * - prontuarios: 7300 dias (20 anos — CFM Resolução 1.821/2007)
     * - consentimentos: 1825 dias (5 anos após término do tratamento)
     * - access_logs: 1825 dias (5 anos)
     * - dados_cadastrais: 1825 dias (5 anos — prazo prescricional geral)
     */
    public function getMinimumRetentionDays(string $category): int
    {
        $minimums = config('lgpd.retention_minimums', [
            'prontuarios' => 7300,
            'consentimentos' => 1825,
            'access_logs' => 1825,
            'dados_cadastrais' => 1825,
        ]);

        return $minimums[$category] ?? 1825;
    }

    /**
     * Retorna a referência legal aplicável à categoria.
     */
    private function getLegalReference(string $category): string
    {
        return match ($category) {
            'prontuarios' => 'CFM Resolução 1.821/2007 — 20 anos',
            'consentimentos' => 'LGPD Art. 16 — 5 anos após término do tratamento',
            'access_logs' => 'Marco Civil da Internet Art. 15 — 5 anos',
            'dados_cadastrais' => 'Código Civil Art. 206 §5º — prazo prescricional geral de 5 anos',
            default => 'Prazo prescricional geral — 5 anos',
        };
    }
}
