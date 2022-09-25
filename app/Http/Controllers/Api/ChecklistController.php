<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ChecklistResource;
use App\Models\Checklist;

class ChecklistController
{

    public function index()
    {
        $checklists = Checklist::all();
        return ChecklistResource::collection($checklists);
    }

    public function show($id)
    {
        $checklist = Checklist::find($id);
        return new ChecklistResource($checklist);
    }

}
