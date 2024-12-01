<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\Kid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChecklistsController extends Controller
{
    public function index(Request $request)
    {
        $checklists = Checklist::all();
        $kid = Kid::find($request->kid);

        if (isset($kid)) {
            // Buscar dados de status para o checklist atual
            $statusAvaliation = DB::table('checklist_competence')
                ->select('note', DB::raw('count(*) as total_competences'))
                ->where('checklist_id', $kid->checklists->last()->id)
                ->groupBy('note')
                ->get();
        }

        return view('checklists.index', compact(
            'checklists',
            'kid',
            'statusAvaliation'
        ));
    }
}
