<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\DomainResource;
use App\Http\Resources\LevelResource;
use App\Models\Level;

class LevelController
{
    public function index()
    {
        $levels = Level::all();
        return LevelResource::collection($levels);
    }

    public function show($id)
    {
        $level = Level::where('id', $id)->first();
        $domains = $level->domains()->orderBy('name')->get();
        return DomainResource::collection($domains);
    }
}
