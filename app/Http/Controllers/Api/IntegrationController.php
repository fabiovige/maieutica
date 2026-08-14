<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfessionalIntegrationResource;
use App\Models\Professional;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    /**
     * Lista profissionais ativos com profissão, nome completo, email e telefone.
     * Endpoint restrito para consumo por sistemas externos (ex: N8N) via token Sanctum.
     */
    public function professionals(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('professional-list-all'), 403);

        $professionals = Professional::query()
            ->with(['specialty', 'user' => function ($query) {
                $query->where('allow', 1);
            }])
            ->whereHas('user', function ($query) {
                $query->where('allow', 1);
            })
            ->get();

        return response()->json([
            'data' => ProfessionalIntegrationResource::collection($professionals),
        ]);
    }
}
