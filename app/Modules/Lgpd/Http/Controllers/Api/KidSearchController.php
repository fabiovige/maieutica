<?php

namespace App\Modules\Lgpd\Http\Controllers\Api;

use App\Models\Kid;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller API para busca de titulares (kids) via Select2 AJAX.
 *
 * Utilizado pelo componente ConsentForm.vue para buscar titulares
 * ao registrar um novo consentimento.
 */
class KidSearchController
{
    /**
     * Busca kids pelo nome (parâmetro ?q=).
     *
     * Retorna no formato esperado pelo Select2:
     * { "data": [{ "id": 1, "name": "Nome" }, ...] }
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        $kids = Kid::where('name', 'LIKE', "%{$query}%")
            ->select(['id', 'name'])
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json(['data' => $kids]);
    }
}
