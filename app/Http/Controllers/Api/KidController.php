<?php

namespace App\Http\Controllers\Api;

use App\Models\Kid;
use App\DTOs\Responses\KidResponseDto;
use App\DTOs\Responses\CollectionResponseDto;
use App\Services\KidService;
use Illuminate\Http\JsonResponse;

class KidController
{
    public function __construct(
        private readonly KidService $kidService
    ) {
    }

    public function index(): JsonResponse
    {
        $kids = $this->kidService->getAllKidsForUser();

        $response = CollectionResponseDto::fromCollection(
            $kids,
            fn ($kid) => KidResponseDto::fromModel($kid)->toMinimalArray()
        );

        return response()->json($response);
    }

    public function show(int $id): JsonResponse
    {
        $kid = $this->kidService->findKidById($id);

        if (!$kid) {
            return response()->json([
                'error' => 'Criança não encontrada',
            ], 404);
        }

        $response = KidResponseDto::fromModel($kid);

        return response()->json($response);
    }

    public function byuser($user_id): JsonResponse
    {
        $kids = Kid::where('user_id', $user_id)
            ->orWhere('created_by', $user_id)
            ->orWhere('responsible_id', $user_id)
            ->get();

        $response = CollectionResponseDto::fromCollection(
            $kids,
            fn ($kid) => KidResponseDto::fromModel($kid)->toCardArray()
        );

        return response()->json($response);
    }
}
