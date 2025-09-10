<?php

declare(strict_types=1);

namespace App\Exceptions\Professional;

use Exception;

class ProfessionalNotFoundException extends Exception
{
    public function __construct(int $id)
    {
        parent::__construct("Profissional com ID {$id} não encontrado", 404);
    }
}
