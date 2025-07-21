<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TutorialController extends Controller
{
    public function index()
    {
        return redirect()->route('tutorial.checklists');
    }

    public function users()
    {
        return view('tutorial.users.index');
    }

    public function checklists()
    {
        return view('tutorial.checklists.index');
    }
}
