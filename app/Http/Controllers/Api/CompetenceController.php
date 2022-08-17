<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompetenceDescriptionResource;
use App\Models\CompetenceDescription;

class CompetenceController extends Controller
{
    public function index()
    {

        $posts = CompetenceDescription::with('competence')
            ->when(request('level'), function ($query) {
                $query->where('level', request('level'));
            });

        return CompetenceDescriptionResource::collection($posts);
    }
}
