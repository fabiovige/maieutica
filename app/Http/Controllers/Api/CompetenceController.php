<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompetenceResource;
use App\Models\Competence;
use Illuminate\Http\Request;

class CompetenceController extends Controller
{
    public function index(Request $request)
    {
        $c = Competence::where('level',  $request->level)->where('domain_id', $request->domain)->get();
        return CompetenceResource::collection($c);
    }
}
