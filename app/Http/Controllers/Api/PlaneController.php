<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PlaneResource;
use App\Models\Plane;

class PlaneController
{

    public function index()
    {
        $planes = Plane::all();
        return PlaneResource::collection($planes);
    }

    public function show($id)
    {
        $plane = Plane::where('kid_id', $id)->orderBy('id', 'DESC')->first();
        return new PlaneResource($plane);
    }

    public function showByKids($id)
    {
        $planes = Plane::where('kid_id', $id)->orderBy('id', 'DESC')->get();
        return PlaneResource::collection($planes);
    }

    public function showCompetences($plane_id)
    {
        $plane = Plane::where('id', $plane_id)->first();
        return new PlaneResource($plane);
    }

    public function storePlane(Request $request)
    {
        dd($request);
    }
}
