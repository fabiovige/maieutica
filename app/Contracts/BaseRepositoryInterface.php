<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?Model;

    public function create(array $data): Model;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findBy(string $field, mixed $value): Collection;

    public function findOneBy(string $field, mixed $value): ?Model;

    public function count(): int;

    public function exists(int $id): bool;
}
