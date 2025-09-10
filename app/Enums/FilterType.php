<?php

declare(strict_types=1);

namespace App\Enums;

enum FilterType: string
{
    case TEXT = 'text';
    case SELECT = 'select';
    case DATE = 'date';
    case NUMBER = 'number';
    case CHECKBOX = 'checkbox';
    case RADIO = 'radio';

    public function getInputClass(): string
    {
        return match ($this) {
            self::TEXT, self::DATE, self::NUMBER => 'form-control',
            self::SELECT => 'form-select',
            self::CHECKBOX, self::RADIO => 'form-check-input',
        };
    }

    public function getWrapperClass(): string
    {
        return match ($this) {
            self::CHECKBOX, self::RADIO => 'form-check',
            default => '',
        };
    }
}
