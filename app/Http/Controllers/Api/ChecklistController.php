<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ChecklistResource;
use App\Models\Checklist;

class ChecklistController
{

    public function index()
    {
        $domains = Checklist::all();
        return ChecklistResource::collection($domains);
    }

    public function show($id)
    {
        $domain = Checklist::find($id);
        return new ChecklistResource($domain);
    }

}
