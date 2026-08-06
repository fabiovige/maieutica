<?php

namespace App\Services\Documents;

use App\Contracts\DocumentTemplate;
use InvalidArgumentException;

class DocumentTemplateFactory
{
    /** @var array<int, class-string<DocumentTemplate>> */
    private const TEMPLATES = [
        1 => Modelo1Template::class,
        2 => Modelo2Template::class,
        3 => Modelo3Template::class,
        4 => Modelo4Template::class,
        5 => Modelo5Template::class,
        6 => Modelo6Template::class,
    ];

    public function make(int $modelType): DocumentTemplate
    {
        $class = self::TEMPLATES[$modelType] ?? null;

        if (! $class) {
            throw new InvalidArgumentException("Modelo de documento desconhecido: {$modelType}");
        }

        return app($class);
    }
}
