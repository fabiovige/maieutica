<?php

declare(strict_types=1);

namespace App\Exceptions\Kid;

use Exception;

class KidUpdateFailedException extends Exception
{
    public function __construct(string $message = "Falha ao atualizar criança", int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}