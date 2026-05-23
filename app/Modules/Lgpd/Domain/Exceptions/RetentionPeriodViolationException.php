<?php

namespace App\Modules\Lgpd\Domain\Exceptions;

use DomainException;

class RetentionPeriodViolationException extends DomainException
{
    private string $category;
    private int $requestedDays;
    private int $minimumDays;

    public function __construct(string $category, int $requestedDays, int $minimumDays)
    {
        $this->category = $category;
        $this->requestedDays = $requestedDays;
        $this->minimumDays = $minimumDays;

        parent::__construct(
            "O período de retenção de {$requestedDays} dias para a categoria '{$category}' é inferior ao mínimo legal de {$minimumDays} dias."
        );
    }

    public static function forCategory(string $category, int $requestedDays, int $minimumDays): self
    {
        return new self($category, $requestedDays, $minimumDays);
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getRequestedDays(): int
    {
        return $this->requestedDays;
    }

    public function getMinimumDays(): int
    {
        return $this->minimumDays;
    }
}
