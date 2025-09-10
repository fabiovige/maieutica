<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

trait HasPagination
{
    /**
     * Aplica paginação padronizada com preservação de query string
     *
     * @param Builder $query
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    protected function paginateWithQueryString(Builder $query, int $perPage = 15): LengthAwarePaginator
    {
        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Aplica filtro de busca genérico
     *
     * @param Builder $query
     * @param string $search
     * @param array $searchFields
     * @return Builder
     */
    protected function applySearchFilter(Builder $query, string $search, array $searchFields): Builder
    {
        return $query->where(function ($q) use ($search, $searchFields) {
            foreach ($searchFields as $field) {
                if (str_contains($field, '.')) {
                    [$relation, $relationField] = explode('.', $field, 2);
                    $q->orWhereHas($relation, function ($subQuery) use ($search, $relationField) {
                        $subQuery->where($relationField, 'LIKE', "%{$search}%");
                    });
                } else {
                    $q->orWhere($field, 'LIKE', "%{$search}%");
                }
            }
        });
    }

    /**
     * Aplica ordenação padronizada
     *
     * @param Builder $query
     * @param array $filters
     * @param array $sortableFields
     * @param string $defaultField
     * @param string $defaultDirection
     * @return Builder
     */
    protected function applySortFilter(
        Builder $query,
        array $filters,
        array $sortableFields = [],
        string $defaultField = 'id',
        string $defaultDirection = 'desc'
    ): Builder {
        $sortBy = $filters['sort_by'] ?? $defaultField;
        $sortDirection = $filters['sort_direction'] ?? $defaultDirection;

        if (!in_array($sortBy, $sortableFields) && !empty($sortableFields)) {
            $sortBy = $defaultField;
        }

        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $sortDirection = $defaultDirection;
        }

        return $query->orderBy($sortBy, $sortDirection);
    }

    /**
     * Extrai filtros padronizados da request
     *
     * @param array $requestData
     * @param int $defaultPerPage
     * @return array
     */
    protected function extractPaginationFilters(array $requestData, int $defaultPerPage = 15): array
    {
        return [
            'search' => $requestData['search'] ?? null,
            'sort_by' => $requestData['sort_by'] ?? null,
            'sort_direction' => $requestData['sort_direction'] ?? 'asc',
            'per_page' => (int) ($requestData['per_page'] ?? $defaultPerPage),
            'page' => (int) ($requestData['page'] ?? 1),
        ];
    }
}
