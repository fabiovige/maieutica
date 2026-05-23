<?php

namespace App\Modules\Lgpd\Domain\Exceptions;

use DomainException;

class InvalidDataRequestTransitionException extends DomainException
{
    private string $currentStatus;
    private string $attemptedStatus;

    public function __construct(string $currentStatus, string $attemptedStatus)
    {
        $this->currentStatus = $currentStatus;
        $this->attemptedStatus = $attemptedStatus;

        parent::__construct(
            "Transição de estado inválida: não é possível alterar de '{$currentStatus}' para '{$attemptedStatus}'."
        );
    }

    public static function forTransition(string $currentStatus, string $attemptedStatus): self
    {
        return new self($currentStatus, $attemptedStatus);
    }

    public function getCurrentStatus(): string
    {
        return $this->currentStatus;
    }

    public function getAttemptedStatus(): string
    {
        return $this->attemptedStatus;
    }
}
