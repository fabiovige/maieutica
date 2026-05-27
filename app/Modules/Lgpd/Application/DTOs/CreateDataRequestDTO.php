<?php

namespace App\Modules\Lgpd\Application\DTOs;

class CreateDataRequestDTO
{
    public function __construct(
        public readonly string $type,
        public readonly string $requesterName,
        public readonly string $requesterDocument,
        public readonly string $contactMethod,
        public readonly int $operatorId,
    ) {}
}
