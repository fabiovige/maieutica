<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Professional;
use App\Models\Specialty;
use Illuminate\Http\Request;

class ProfessionalController extends Controller
{
    public function index()
    {
        $professionals = Professional::with(['user', 'specialty', 'kids'])
            ->whereHas('user', function($q) {
                $q->whereHas('roles', function($q) {
                    $q->where('name', 'professional');
                });
            })
            ->get();

        return view('professionals.index', compact('professionals'));
    }

    public function show(Professional $professional)
    {
        $professional->load(['user', 'specialty', 'kids']);
        return view('professionals.show', compact('professional'));
    }
}
