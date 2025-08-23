<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\UserCreationStrategies\DefaultUserCreationStrategy;
use App\Services\UserCreationStrategies\ProfessionalUserCreationStrategy;
use App\Services\UserCreationStrategies\UserCreationStrategyInterface;

class UserCreationStrategyFactory
{
    private array $strategies;

    public function __construct()
    {
        $this->strategies = [
            new ProfessionalUserCreationStrategy(),
            new DefaultUserCreationStrategy(),
        ];
    }

    public function getStrategy(string $roleName): UserCreationStrategyInterface
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($roleName)) {
                return $strategy;
            }
        }

        // Fallback para estratégia padrão
        return new DefaultUserCreationStrategy();
    }
}