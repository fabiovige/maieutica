<?php

declare(strict_types=1);

namespace App\DTOs\Responses;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

abstract class AbstractResponseDto implements Arrayable, Jsonable, JsonSerializable
{
    abstract public function toArray(): array;

    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    protected function formatDateTime(?\DateTime $date, string $format = 'Y-m-d H:i:s'): ?string
    {
        return $date?->format($format);
    }

    protected function formatDate(?\DateTime $date, string $format = 'Y-m-d'): ?string
    {
        return $date?->format($format);
    }

    protected function formatCurrency(?float $value): ?string
    {
        return $value ? number_format($value, 2, ',', '.') : null;
    }

    protected function formatBoolean(?bool $value): ?string
    {
        return $value === null ? null : ($value ? 'Sim' : 'Não');
    }
}
