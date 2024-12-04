<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\Kid;
use App\Models\Plane;
use Illuminate\Http\Request;

class PlaneAutomaticController extends Controller
{
    public function index($kidId = null, $checklistId = null)
    {
        if(!$kidId && !$checklistId) {
            return redirect()->back()->with('error', 'ID não informado');
        }

        $kid = Kid::find($kidId);
        $checklist = Checklist::find($checklistId);
        $statusAvaliation = $checklist->getStatusAvaliation($checklistId);
        $planes = $checklist->planes()->orderBy('created_at', 'desc')->get();
        $plane = Plane::where('kid_id', $kid->id)->where('checklist_id', $checklist->id)->where('is_active', true)->first();

        // Adiciona as descrições das notas
        $notesDescription = [
            0 => 'Não observado',
            1 => 'Mais ou menos',
            2 => 'Difícil de obter',
            3 => 'Consistente'
        ];


        return view('plane_automatic.index', compact('kid', 'checklist', 'statusAvaliation', 'notesDescription', 'planes', 'plane'));
    }
}
