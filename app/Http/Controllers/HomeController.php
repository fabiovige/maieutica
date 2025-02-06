<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        //$this->middleware('acl');
    }

    public function index()
    {
        return view('home');
    }
}
