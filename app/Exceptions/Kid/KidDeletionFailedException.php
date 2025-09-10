<?php

declare(strict_types=1);

namespace App\Exceptions\Kid;

use Exception;

class KidDeletionFailedException extends Exception
{
    public function __construct(string $message = "Falha ao excluir criança", int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}