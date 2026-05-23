<?php

namespace App\Modules\Lgpd\Domain\Exceptions;

use DomainException;

class DuplicateActiveConsentException extends DomainException
{
    private int $subjectId;
    private string $purpose;

    public function __construct(int $subjectId, string $purpose)
    {
        $this->subjectId = $subjectId;
        $this->purpose = $purpose;

        parent::__construct(
            "Já existe um consentimento ativo para o titular #{$subjectId} com a finalidade '{$purpose}'."
        );
    }

    public static function forSubjectAndPurpose(int $subjectId, string $purpose): self
    {
        return new self($subjectId, $purpose);
    }

    public function getSubjectId(): int
    {
        return $this->subjectId;
    }

    public function getPurpose(): string
    {
        return $this->purpose;
    }
}
