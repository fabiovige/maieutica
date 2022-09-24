<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checklist;
use Illuminate\Http\Request;

class ChartController extends Controller
{
    public function percentage(Request $request)
    {
        $result = Checklist::calculatePercentage($request->all());

        $arrNote = [];
        $arrColor = [];
        foreach($result as $c=>$v){
            if($v->note === 1){
                $arrNote[$v->checklist_id][$v->level_id][$v->initial][] = 0;
            } else {
                $arrNote[$v->checklist_id][$v->level_id][$v->initial][] = $v->note;
            }
            $arrColor[$v->initial] = $v->color;
        }

        $arrDomain = [];
        foreach($arrNote as $checklist_id => $levels){
            foreach($levels as $level_id => $domains) {
                foreach($domains as $initial=> $notes){
                    $qtde = count($notes);
                    $soma = array_sum($notes);
                    $arrDomain[$checklist_id][$initial][] = ceil(( $soma * 100 ) / ( $qtde * 3 ) );
                }
            }
        }

        $arrDomain2 = [];
        foreach($arrDomain as $checklist_id => $domains) {
            foreach($domains as $initial=> $notes){
                $qtde = count($notes);
                $soma = array_sum($notes);
                $arrDomain2['note'][] = ceil(($soma / $qtde));
                $arrDomain2['age'][] = 100;
                $arrDomain2['initial'][] = $initial;
                $arrDomain2['color'][] = $arrColor[$initial];
            }
        }

        return response()->json($arrDomain2, 200);
    }

}
