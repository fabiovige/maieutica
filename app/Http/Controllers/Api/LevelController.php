<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\LevelResource;
use App\Models\Level;
use Illuminate\Http\Request;

class LevelController
{
    public function index()
    {
        $levels = Level::all();
        return LevelResource::collection($levels);
    }

    public function show($id)
    {
        $level = Level::where('id', $id)->get();
        return LevelResource::collection($level);
    }
}
