<?php

namespace App\Repositories;

use App\Interfaces\Repositories\KidRepositoryInterface;
use App\Models\Kid;
use App\Models\Checklist;
use Illuminate\Support\Facades\Auth;

class KidRepository implements KidRepositoryInterface
{
    protected $model;

    public function __construct(Kid $model)
    {
        $this->model = $model;
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function getKids()
    {
        return Kid::getKids();
    }

    public function create(array $data)
    {
        if (Auth::user()->hasRole('professional')) {
            $data['profession_id'] = Auth::user()->id;
        }

        return $this->model->forProfessional()->create($data);
    }

    public function update($kid, array $data)
    {
        return $kid->update($data);
    }

    public function delete($kid)
    {
        return $kid->delete();
    }

    public function getKidWithChecklists($kidId)
    {
        return $this->model->with('checklists')->findOrFail($kidId);
    }

    public function getCurrentChecklist($kidId)
    {
        return Checklist::where('kid_id', $kidId)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getPreviousChecklists($kidId, $currentChecklistId)
    {
        return Checklist::where('kid_id', $kidId)
            ->where('id', '<>', $currentChecklistId)
            ->orderBy('id', 'desc')
            ->get();
    }
}
