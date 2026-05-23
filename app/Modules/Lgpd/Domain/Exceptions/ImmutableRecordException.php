<?php

namespace App\Modules\Lgpd\Domain\Exceptions;

use DomainException;

class ImmutableRecordException extends DomainException
{
    private int $recordId;
    private string $operation;

    public function __construct(int $recordId, string $operation)
    {
        $this->recordId = $recordId;
        $this->operation = $operation;

        parent::__construct(
            "O registro de acesso #{$recordId} é imutável e não pode ser {$operation}."
        );
    }

    public static function forAccessLog(int $recordId, string $operation = 'alterado'): self
    {
        return new self($recordId, $operation);
    }

    public function getRecordId(): int
    {
        return $this->recordId;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }
}
