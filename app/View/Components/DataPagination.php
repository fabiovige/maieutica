<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\Component;
use Illuminate\View\View;

class DataPagination extends Component
{
    public LengthAwarePaginator $paginator;
    public int $defaultPerPage;
    public array $perPageOptions;

    public function __construct(
        LengthAwarePaginator $paginator,
        int $defaultPerPage = 10,
        array $perPageOptions = [5, 10, 15, 25, 50]
    ) {
        $this->paginator = $paginator;
        $this->defaultPerPage = $defaultPerPage;
        $this->perPageOptions = $perPageOptions;
    }

    public function render(): View
    {
        return view('components.data-pagination');
    }

    public function getCurrentPerPage(): int
    {
        return $this->paginator->perPage();
    }

    public function hasResults(): bool
    {
        return $this->paginator->total() > 0;
    }

    public function getResultsText(): string
    {
        if (!$this->hasResults()) {
            return 'Nenhum resultado encontrado';
        }

        return sprintf(
            'Mostrando %d a %d de %d resultados',
            $this->paginator->firstItem() ?? 0,
            $this->paginator->lastItem() ?? 0,
            $this->paginator->total()
        );
    }

    public function shouldShowPagination(): bool
    {
        return $this->hasResults() && $this->paginator->hasPages();
    }

    public function getPerPageSelectId(): string
    {
        return 'per_page_pagination_' . uniqid();
    }
}