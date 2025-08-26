<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Enums\FilterType;
use Illuminate\View\Component;
use Illuminate\View\View;

class DataFilter extends Component
{
    public array $filters;
    public string $actionRoute;
    public array $hiddenFields;
    public ?int $totalResults;
    public string $entityName;

    public function __construct(
        array $filters = [],
        string $actionRoute = '',
        array $hiddenFields = [],
        ?int $totalResults = null,
        string $entityName = 'item'
    ) {
        $this->filters = $this->processFilters($filters);
        $this->actionRoute = $actionRoute;
        $this->hiddenFields = $hiddenFields;
        $this->totalResults = $totalResults;
        $this->entityName = $entityName;
    }

    public function render(): View
    {
        return view('components.data-filter');
    }

    private function processFilters(array $filters): array
    {
        return array_map(function ($filter) {
            return array_merge([
                'type' => FilterType::TEXT->value,
                'name' => '',
                'label' => '',
                'placeholder' => '',
                'value' => '',
                'options' => [],
                'class' => 'col-md-6',
                'required' => false,
                'attributes' => [],
            ], $filter);
        }, $filters);
    }

    public function getFilterTypeEnum(string $type): FilterType
    {
        return FilterType::from($type);
    }

    public function hasActiveFilters(): bool
    {
        foreach ($this->filters as $filter) {
            if (!empty($filter['value'])) {
                return true;
            }
        }
        return false;
    }

    public function getClearFiltersUrl(): string
    {
        $queryParams = [];
        foreach ($this->hiddenFields as $key => $value) {
            $queryParams[$key] = $value;
        }
        
        return route($this->actionRoute, $queryParams);
    }
}