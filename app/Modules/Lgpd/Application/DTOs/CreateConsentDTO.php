<?php

namespace App\Modules\Lgpd\Application\DTOs;

class CreateConsentDTO
{
    public function __construct(
        public readonly int $subjectId,
        public readonly string $subjectType,
        public readonly string $purpose,
        public readonly string $legalBasis,
        public readonly int $termVersion,
        public readonly int $operatorId,
    ) {}
}
