<?php

namespace App\Http\Controllers;

use App\Http\Requests\KidRequest;
use App\Services\KidService;
use Illuminate\Http\Request;

class KidController extends Controller
{
    protected $kidService;

    public function __construct(KidService $kidService)
    {
        $this->kidService = $kidService;
    }

    public function index()
    {
        $kids = $this->kidService->getAllKids();
        return view('kids.index', compact('kids'));
    }

    public function store(KidRequest $request)
    {
        $this->kidService->createKid($request->validated());
        return redirect()->route('kids.index');
    }

    // Adicione outros métodos conforme necessário
}
