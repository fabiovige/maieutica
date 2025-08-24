<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

abstract class BaseController extends Controller
{
    protected const DEFAULT_PER_PAGE = 10;
    protected const ALLOWED_PER_PAGE = [5, 10, 15, 25, 50];

    protected function buildFilters(Request $request, array $defaultFilters = []): array
    {
        $perPage = (int) $request->get('per_page', self::DEFAULT_PER_PAGE);

        if (!in_array($perPage, self::ALLOWED_PER_PAGE)) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        return array_merge($defaultFilters, [
            'search' => $request->get('search'),
            'sort_by' => $request->get('sort_by', 'name'),
            'sort_direction' => $request->get('sort_direction', 'asc'),
            'per_page' => $perPage,
            'page' => $request->get('page', 1),
        ]);
    }

    protected function handleIndexRequest(Request $request, callable $dataCallback, string $viewName, array $additionalData = [], string $dataKey = null): mixed
    {
        try {
            if ($request->ajax()) {
                return $this->handleAjaxIndexRequest($request, $dataCallback);
            }

            $filters = $this->buildFilters($request);
            $data = $dataCallback($filters);

            // Determina a chave baseada no nome da view se não fornecida
            if ($dataKey === null) {
                $dataKey = $this->getDataKeyFromViewName($viewName);
            }

            return \view($viewName, array_merge([
                $dataKey => $data,
                'filters' => $filters,
                'defaultPerPage' => self::DEFAULT_PER_PAGE,
            ], $additionalData));
        } catch (Exception $e) {
            $message = 'Erro ao carregar lista: ' . $e->getMessage() . ' | User:' . Auth::user()->name . '(ID:' . Auth::user()->id . ')';
            Log::error($message);

            \flash('Erro ao carregar a lista. Tente novamente.')->error();

            if ($dataKey === null) {
                $dataKey = $this->getDataKeyFromViewName($viewName);
            }

            return \view($viewName, array_merge([
                $dataKey => \collect(),
                'filters' => [],
                'defaultPerPage' => self::DEFAULT_PER_PAGE,
            ], $additionalData));
        }
    }

    private function getDataKeyFromViewName(string $viewName): string
    {
        // Extrai o nome do módulo da view (ex: 'users.index' -> 'users')
        $parts = explode('.', $viewName);
        return $parts[0] ?? 'data';
    }

    private function handleAjaxIndexRequest(Request $request, callable $dataCallback): mixed
    {
        try {
            $filters = $this->buildFilters($request);
            $data = $dataCallback($filters);

            $responseData = [
                'items' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'has_more_pages' => $data->hasMorePages(),
                ],
            ];

            return \response()->json($responseData);
        } catch (Exception $e) {
            $message = 'Erro ao carregar dados AJAX: ' . $e->getMessage() . ' | User:' . Auth::user()->name . '(ID:' . Auth::user()->id . ')';
            Log::error($message);

            return \response()->json([
                'error' => 'Erro ao carregar dados. Tente novamente.',
            ], 500);
        }
    }

    protected function handleViewRequest(callable $dataCallback, string $viewName, array $additionalData = [], string $errorMessage = 'Erro ao carregar dados.', string $redirectRoute = null): mixed
    {
        try {
            $data = $dataCallback();

            if (is_array($data)) {
                return \view($viewName, \array_merge($data, $additionalData));
            }

            if (!$data) {
                \flash('Item não encontrado.')->error();
                return \redirect()->route($redirectRoute ?: $this->getDefaultIndexRoute());
            }

            return \view($viewName, \array_merge(compact('data'), $additionalData));
        } catch (Exception $e) {
            Log::error($errorMessage . ': ' . $e->getMessage());
            \flash($errorMessage)->error();
            return \redirect()->route($redirectRoute ?: $this->getDefaultIndexRoute());
        }
    }

    protected function handleCreateRequest(callable $dataCallback, string $viewName, array $additionalData = [], string $errorMessage = 'Erro ao carregar formulário.', string $redirectRoute = null): mixed
    {
        try {
            $data = $dataCallback();
            return \view($viewName, \array_merge($data, $additionalData));
        } catch (Exception $e) {
            Log::error($errorMessage . ': ' . $e->getMessage());
            \flash($errorMessage)->error();
            return \redirect()->route($redirectRoute ?: $this->getDefaultIndexRoute());
        }
    }

    protected function handleStoreRequest(callable $storeCallback, string $successMessage = 'Item criado com sucesso.', string $errorMessage = 'Erro ao criar item.', string $redirectRoute = null): mixed
    {
        try {
            $storeCallback();
            \flash($successMessage)->success();
            return \redirect()->route($redirectRoute ?: $this->getDefaultIndexRoute());
        } catch (Exception $e) {
            Log::error($errorMessage . ': ' . $e->getMessage());
            \flash($errorMessage)->error();
            return \redirect()->back()->withInput();
        }
    }

    protected function handleUpdateRequest(callable $updateCallback, string $successMessage = 'Item atualizado com sucesso.', string $errorMessage = 'Erro ao atualizar item.', string $redirectRoute = null): mixed
    {
        try {
            $updateCallback();
            \flash($successMessage)->success();
            return \redirect()->route($redirectRoute ?: $this->getDefaultIndexRoute());
        } catch (Exception $e) {
            Log::error($errorMessage . ': ' . $e->getMessage());
            \flash($errorMessage)->error();
            return \redirect()->back()->withInput();
        }
    }

    private function getDefaultIndexRoute(): string
    {
        $className = class_basename($this);
        $routeName = strtolower(str_replace('Controller', '', $className));
        return $routeName . '.index';
    }
}
