<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\KidResource;
use App\Models\Kid;

class KidController
{
    public function index()
    {
        $kids = Kid::all();
        return KidResource::collection($kids);
    }

    public function byuser($user_id)
    {
        $kids = Kid::where('user_id', $user_id)
            ->orWhere('created_by', $user_id)
            ->orWhere('responsible_id', $user_id)
            ->get();
        return KidResource::collection($kids);
    }
}
