<?php

declare(strict_types=1);

namespace App\Exceptions\Professional;

use Exception;

class UserNotLinkedToProfessionalException extends Exception
{
    public function __construct(int $professionalId)
    {
        parent::__construct("Nenhum usuário vinculado ao profissional ID {$professionalId}");
    }
}