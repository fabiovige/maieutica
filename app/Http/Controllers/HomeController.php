<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware('acl');
    }

    public function index()
    {
        return view('home');
    }
}
