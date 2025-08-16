<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\Kid;
use App\Models\Plane;

class PlaneAutomaticController extends Controller
{
    public function index($kidId = null, $checklistId = null)
    {
        if (!$kidId && !$checklistId) {
            return redirect()->back()->with('error', 'ID não informado');
        }

        $kid = Kid::find($kidId);
        $checklist = Checklist::find($checklistId);
        $statusAvaliation = $checklist->getStatusAvaliation($checklistId);
        $planes = $checklist->planes()->orderBy('created_at', 'desc')->get();
        $plane = Plane::where('kid_id', $kid->id)->where('checklist_id', $checklist->id)->where('is_active', true)->first();

        // Adiciona as descrições das notas
        $notesDescription = [
            0 => 'Não Observado',
            1 => 'Não desenvolvimento',
            2 => 'Em desenvolvimento',
            3 => 'Desenvolvido',
        ];

        $data = [
            'kid' => $kid,
            'checklist' => $checklist,
            'statusAvaliation' => $statusAvaliation,
            'notesDescription' => $notesDescription,
            'planes' => $planes,
            'plane' => $plane,
            'data' => now(),
        ];

        return view('plane_automatic.index', $data);
    }
}
