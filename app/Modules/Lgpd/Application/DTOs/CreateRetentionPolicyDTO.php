<?php

namespace App\Modules\Lgpd\Application\DTOs;

class CreateRetentionPolicyDTO
{
    public function __construct(
        public readonly string $category,
        public readonly int $retentionDays,
        public readonly string $expirationAction,
        public readonly int $operatorId,
    ) {}
}
