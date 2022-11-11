<?php

namespace App\Http\Controllers;

use App\Models\Kid;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $kids = Kid::getKids();
        $data = [];
        foreach($kids->get() as $kid) {
            $data['countChecklists'][$kid->id] = $kid->checklists()->count();
            $data['countPlanes'][$kid->id] = $kid->planes()->count();
        }
        $data['kids'] = $kids->get();
        return view('home', $data);
    }
}
