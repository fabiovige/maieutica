<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\Kid;
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


        // Adiciona as descrições das notas
        $notesDescription = [
            0 => 'Não observado',
            1 => 'Mais ou menos',
            2 => 'Difícil de obter',
            3 => 'Consistente'
        ];

        return view('plane_automatic.index', compact('kid', 'checklist', 'statusAvaliation', 'notesDescription'));
    }
}
