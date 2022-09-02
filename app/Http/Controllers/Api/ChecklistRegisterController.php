<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChecklistRegisterRequest;
use App\Http\Resources\ChecklistRegisterResource;
use App\Models\ChecklistRegister;

class ChecklistRegisterController extends Controller
{

    public function index()
    {
        $getChecklistRegister = ChecklistRegister::where('checklist_id', $request->checklist_id)
            ->where('competence_description_id', $request->competence_description_id);

    }

    public function store(StoreChecklistRegisterRequest $request): ChecklistRegisterResource
    {
        $getChecklistRegister = ChecklistRegister::where('checklist_id', $request->checklist_id)
            ->where('competence_description_id', $request->competence_description_id);

        if($getChecklistRegister->count()) {
            $id = $getChecklistRegister->first()->id;
            $checklistRegister = ChecklistRegister::findOrFail($id);
            $checklistRegister->update($request->all());
        } else {
            $checklistRegister = ChecklistRegister::create($request->all());
        }

        return new ChecklistRegisterResource($checklistRegister);
    }
}
