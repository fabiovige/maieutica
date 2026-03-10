<?php

namespace App\Http\Controllers;

use App\Models\Release;

class ReleaseController extends Controller
{
    public function index()
    {
        $releases = Release::orderBy('release_date', 'desc')->get();

        return view('releases.index', compact('releases'));
    }

    public function show(Release $release)
    {
        return view('releases.show', compact('release'));
    }
}
