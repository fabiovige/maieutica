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
        $data = [
            'kids' => $kids->get()
        ];
        return view('home', $data);
    }
}
