<?php

declare(strict_types=1);

namespace App\Exceptions\Professional;

use Exception;

class UserAssociationNotFoundException extends Exception
{
    public function __construct(int $professionalId)
    {
        parent::__construct("Usuário associado ao profissional ID {$professionalId} não encontrado", 404);
    }
}
