<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChecklistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    public function createChecklist($id)
    {
        try {
            $kid = Kid::findOrFail($id);
            $kid->birth_date = Carbon::createFromFormat('Y-m-d', $kid->birth_date)->format('d/m/Y');
            $message = label_case('Create checklist ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            return view('checklists.create', [
                'kid' => $kid,
            ]);
        } catch (Exception $e) {
            flash(self::MSG_NOT_FOUND)->warning();
            $message = label_case('Create checklist '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::warning($message);

            return redirect()->route('kids.index');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
