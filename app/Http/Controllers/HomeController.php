<?php

namespace App\Http\Controllers;

use App\Models\Kid;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('acl');
    }

    public function index()
    {
        $kids = Kid::getKids()->get();
        $data = [];
        foreach($kids as $key => $kid) {
            $kids[$key]['months'] = $kid->months;
            $data['countChecklists'][$kid->id] = $kid->checklists()->count();
            $data['countPlanes'][$kid->id] = $kid->planes()->count();
        }
        $data['kids'] = $kids;
        return view('home', $data);
    }
}
