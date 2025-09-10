<?php

declare(strict_types=1);

namespace App\Exceptions\Professional;

use Exception;

class ProfessionalCreationFailedException extends Exception
{
    public function __construct(string $message = 'Falha ao criar profissional', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
