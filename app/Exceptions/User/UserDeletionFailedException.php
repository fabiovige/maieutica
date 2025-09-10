<?php

declare(strict_types=1);

namespace App\Exceptions\User;

use Exception;

class UserDeletionFailedException extends Exception
{
    public function __construct(string $message = "Falha ao excluir usuário", int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}