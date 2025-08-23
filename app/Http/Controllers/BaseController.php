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
        ]);
    }

    protected function handleIndexRequest(Request $request, callable $dataCallback, string $viewName, array $additionalData = []): mixed
    {
        try {
            if ($request->ajax()) {
                return $this->handleAjaxIndexRequest($request, $dataCallback);
            }

            $filters = $this->buildFilters($request);
            $data = $dataCallback($filters);

            return \view($viewName, array_merge([
                'kids' => $data,
                'filters' => $filters,
                'defaultPerPage' => self::DEFAULT_PER_PAGE,
            ], $additionalData));
        } catch (Exception $e) {
            $message = 'Erro ao carregar lista: ' . $e->getMessage() . ' | User:' . Auth::user()->name . '(ID:' . Auth::user()->id . ')';
            Log::error($message);

            \flash('Erro ao carregar a lista. Tente novamente.')->error();

            return \view($viewName, array_merge([
                'kids' => \collect(),
                'filters' => [],
                'defaultPerPage' => self::DEFAULT_PER_PAGE,
            ], $additionalData));
        }
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
}
