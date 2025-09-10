<?php

declare(strict_types=1);

namespace App\DTOs\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CollectionResponseDto extends AbstractResponseDto
{
    public function __construct(
        public array $data,
        public int $total,
        public ?int $perPage = null,
        public ?int $currentPage = null,
        public ?int $lastPage = null,
        public ?string $nextPageUrl = null,
        public ?string $prevPageUrl = null,
        public array $meta = []
    ) {
    }

    public static function fromPaginator(LengthAwarePaginator $paginator, callable $transformer = null): self
    {
        $data = $paginator->getCollection();
        
        if ($transformer) {
            $data = $data->map($transformer)->toArray();
        } else {
            $data = $data->toArray();
        }

        return new self(
            data: $data,
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
            lastPage: $paginator->lastPage(),
            nextPageUrl: $paginator->nextPageUrl(),
            prevPageUrl: $paginator->previousPageUrl(),
            meta: [
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'path' => $paginator->path(),
                'has_pages' => $paginator->hasPages(),
                'has_more_pages' => $paginator->hasMorePages(),
            ]
        );
    }

    public static function fromCollection(Collection $collection, callable $transformer = null): self
    {
        $data = $transformer ? $collection->map($transformer)->toArray() : $collection->toArray();

        return new self(
            data: $data,
            total: $collection->count()
        );
    }

    public static function empty(): self
    {
        return new self(
            data: [],
            total: 0
        );
    }

    public function toArray(): array
    {
        $response = [
            'data' => $this->data,
            'total' => $this->total,
        ];

        if ($this->isPaginated()) {
            $response['pagination'] = [
                'per_page' => $this->perPage,
                'current_page' => $this->currentPage,
                'last_page' => $this->lastPage,
                'total' => $this->total,
                'next_page_url' => $this->nextPageUrl,
                'prev_page_url' => $this->prevPageUrl,
            ];

            $response['meta'] = $this->meta;
        }

        return $response;
    }

    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    public function isPaginated(): bool
    {
        return $this->perPage !== null;
    }

    public function hasNextPage(): bool
    {
        return $this->nextPageUrl !== null;
    }

    public function hasPreviousPage(): bool
    {
        return $this->prevPageUrl !== null;
    }
}