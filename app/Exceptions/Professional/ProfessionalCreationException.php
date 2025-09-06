<?php

declare(strict_types=1);

namespace App\Exceptions\Professional;

use Exception;

class ProfessionalCreationException extends Exception
{
    public function __construct(string $message = "Erro ao criar profissional", ?Exception $previous = null)
    {
        parent::__construct($message, 500, $previous);
    }
}