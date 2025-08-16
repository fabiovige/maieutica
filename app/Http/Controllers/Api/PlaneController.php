<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PlaneResource;
use App\Models\CompetencePlane;
use App\Models\Plane;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlaneController
{
    public function index()
    {
        $planes = Plane::all();

        return PlaneResource::collection($planes);
    }

    public function show($id)
    {
        $plane = Plane::where('kid_id', '=', $id)
            ->orderBy('id', 'DESC')->first();

        return new PlaneResource($plane);
    }

    public function showByKids($id, $checklist_id)
    {
        $planes = Plane::where('kid_id', $id)
            ->where('checklist_id', $checklist_id)
            ->orderBy('id', 'DESC')->get();

        return PlaneResource::collection($planes);
    }

    public function showCompetences($plane_id)
    {
        $plane = Plane::where('id', $plane_id)->first();

        return new PlaneResource($plane);
    }

    public function storePlane(Request $request)
    {
        $plane = Plane::where('id', $request->plane_id)->first();
        $arrCompetences = $plane->competences()->pluck('id')->toArray();
        $arrCompetences[] = (int) $request->competence_id;
        $plane->competences()->sync($arrCompetences);

        return new PlaneResource($plane);
    }

    public function deletePlane(Request $request)
    {
        return CompetencePlane::deleteCompetencePlane($request);
    }

    public function newPlane(Request $request)
    {
        $data = $request->all();
        $data['created_by'] = Auth::id();
        $plane = Plane::create($data);

        return new PlaneResource($plane);
    }
}
