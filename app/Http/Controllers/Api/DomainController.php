<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\DomainResource;
use App\Models\Domain;

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
}
