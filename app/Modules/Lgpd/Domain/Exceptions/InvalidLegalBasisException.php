<?php

namespace App\Modules\Lgpd\Domain\Exceptions;

use DomainException;

class InvalidLegalBasisException extends DomainException
{
    private string $invalidBasis;

    public function __construct(string $invalidBasis)
    {
        $this->invalidBasis = $invalidBasis;

        parent::__construct(
            "A base legal '{$invalidBasis}' não é válida. Utilize uma das bases legais previstas nos Art. 7 e Art. 11 da LGPD."
        );
    }

    public static function forBasis(string $invalidBasis): self
    {
        return new self($invalidBasis);
    }

    public function getInvalidBasis(): string
    {
        return $this->invalidBasis;
    }
}
