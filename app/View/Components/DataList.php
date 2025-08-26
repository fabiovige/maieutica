<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Enums\ListActionType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

class DataList extends Component
{
    public LengthAwarePaginator|Collection $data;
    public array $columns;
    public array $actions;
    public ?string $emptyMessage;
    public ?string $emptyWithFiltersMessage;
    public bool $hasFiltersApplied;
    public ?string $clearFiltersUrl;

    public function __construct(
        LengthAwarePaginator|Collection $data,
        array $columns = [],
        array $actions = [],
        ?string $emptyMessage = null,
        ?string $emptyWithFiltersMessage = null,
        bool $hasFiltersApplied = false,
        ?string $clearFiltersUrl = null
    ) {
        $this->data = $data;
        $this->columns = $this->processColumns($columns);
        $this->actions = $this->processActions($actions);
        $this->emptyMessage = $emptyMessage ?? 'Nenhum registro encontrado.';
        $this->emptyWithFiltersMessage = $emptyWithFiltersMessage ?? 'Nenhum registro encontrado com os filtros aplicados.';
        $this->hasFiltersApplied = $hasFiltersApplied;
        $this->clearFiltersUrl = $clearFiltersUrl;
    }

    public function render(): View
    {
        return view('components.data-list', [
            'data' => $this->data,
            'columns' => $this->columns,
            'actions' => $this->actions,
            'emptyMessage' => $this->emptyMessage,
            'emptyWithFiltersMessage' => $this->emptyWithFiltersMessage,
            'hasFiltersApplied' => $this->hasFiltersApplied,
            'clearFiltersUrl' => $this->clearFiltersUrl,
        ]);
    }

    public function isEmpty(): bool
    {
        return $this->data->isEmpty();
    }

    public function hasActions(): bool
    {
        return !empty($this->actions);
    }

    public function getColumnValue($item, array $column): mixed
    {
        if (isset($column['callback']) && is_callable($column['callback'])) {
            return $column['callback']($item);
        }

        if (isset($column['attribute'])) {
            // Usar data_get do Laravel para acessar propriedades aninhadas como 'kid.name'
            return data_get($item, $column['attribute']) ?? '';
        }

        return '';
    }

    public function getActionUrl(array $action, $item): ?string
    {
        if (isset($action['url_callback']) && is_callable($action['url_callback'])) {
            return $action['url_callback']($item);
        }

        if (isset($action['route'])) {
            $routeParams = [];
            if (isset($action['route_params']) && !empty($action['route_params'])) {
                foreach ($action['route_params'] as $param => $attribute) {
                    $routeParams[$param] = data_get($item, $attribute);
                }
            } else {
                // Se não houver route_params, usa o ID do item
                $routeParams = $item->id;
            }
            
            return route($action['route'], $routeParams);
        }

        return $action['url'] ?? '#';
    }

    public function shouldShowAction(array $action, $item): bool
    {
        if (isset($action['permission']) && !auth()->user()?->can($action['permission'], $item)) {
            return false;
        }

        if (isset($action['condition_callback']) && is_callable($action['condition_callback'])) {
            return $action['condition_callback']($item);
        }

        return $action['visible'] ?? true;
    }

    private function processColumns(array $columns): array
    {
        return array_map(function ($column) {
            if (is_string($column)) {
                return [
                    'label' => $column,
                    'attribute' => strtolower($column),
                    'sortable' => false,
                    'class' => '',
                ];
            }

            return array_merge([
                'label' => '',
                'attribute' => '',
                'sortable' => false,
                'class' => '',
                'callback' => null,
            ], $column);
        }, $columns);
    }

    private function processActions(array $actions): array
    {
        return array_map(function ($action) {
            if (is_string($action)) {
                $actionType = ListActionType::from($action);
                return [
                    'type' => $action,
                    'label' => $actionType->getDefaultLabel(),
                    'icon' => $actionType->getIcon(),
                    'class' => $actionType->getBootstrapClass(),
                    'route' => null,
                    'url' => null,
                    'permission' => null,
                    'visible' => true,
                ];
            }

            $type = $action['type'] ?? ListActionType::CUSTOM->value;
            $actionType = ListActionType::from($type);

            return array_merge([
                'type' => $type,
                'label' => $actionType->getDefaultLabel(),
                'icon' => $actionType->getIcon(),
                'class' => $actionType->getBootstrapClass(),
                'route' => null,
                'route_params' => [],
                'url' => null,
                'url_callback' => null,
                'permission' => null,
                'condition_callback' => null,
                'visible' => true,
                'attributes' => [],
            ], $action);
        }, $actions);
    }
}