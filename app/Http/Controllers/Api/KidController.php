<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KidResource;
use App\Models\Kid;

class KidController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Kid::class);

        // getKids() aplica o escopo de visibilidade por permissions:
        // kid-list-all ve todos; kid-list ve os seus; demais veem apenas
        // os kids sob sua responsabilidade.
        $kids = Kid::getKids();

        return KidResource::collection($kids);
    }

    public function byuser($user_id)
    {
        $this->authorize('viewAny', Kid::class);

        // Sem permission global, o usuario so pode consultar os proprios vinculos
        if (! auth()->user()->can('kid-list-all') && (int) $user_id !== auth()->id()) {
            abort(403, 'Nao autorizado a consultar pacientes de outro usuario.');
        }

        $kids = Kid::where(function ($query) use ($user_id) {
            $query->where('user_id', $user_id)
                ->orWhere('created_by', $user_id)
                ->orWhere('responsible_id', $user_id);
        })->get();

        return KidResource::collection($kids);
    }
}
