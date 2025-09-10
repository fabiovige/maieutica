<?php

declare(strict_types=1);

namespace App\Exceptions\Responsible;

use Exception;

class ResponsibleCreationFailedException extends Exception
{
    public function __construct(string $message = "Falha ao criar responsável", int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}