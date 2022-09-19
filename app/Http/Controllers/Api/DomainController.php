<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\DomainResource;
use App\Models\Domain;
use App\Models\Level;
use Illuminate\Support\Facades\DB;

class DomainController
{
    public function index()
    {
        $domains = Domain::all();
        return DomainResource::collection($domains);
    }

    public function show($id)
    {
        $domain = Domain::find($id);
        return new DomainResource($domain);
    }

    public function getInitials($level_id = 1)
    {
        return Level::with('domains')->findOrFail($level_id);
    }
}
