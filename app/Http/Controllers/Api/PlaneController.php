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
        $validated = $request->validate([
            'plane_id' => 'required|exists:planes,id',
            'competence_id' => 'required|exists:competences,id',
            'kid_id' => 'required|exists:kids,id',
        ]);

        $plane = Plane::findOrFail($validated['plane_id']);

        // Verifica se o plano pertence à criança
        if ($plane->kid_id != $validated['kid_id']) {
            return response()->json([
                'error' => 'Este plano não pertence a esta criança'
            ], 403);
        }

        $arrCompetences = $plane->competences()->pluck('id')->toArray();

        // Evita duplicatas
        if (!in_array($validated['competence_id'], $arrCompetences)) {
            $arrCompetences[] = (int) $validated['competence_id'];
            $plane->competences()->sync($arrCompetences);
        }

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
