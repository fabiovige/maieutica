<?php

namespace App\Interfaces\Repositories;

interface KidRepositoryInterface
{
    public function findById($id);
    public function getKids();
    public function create(array $data);
    public function update($kid, array $data);
    public function delete($kid);
    public function getKidWithChecklists($kidId);
    public function getCurrentChecklist($kidId);
    public function getPreviousChecklists($kidId, $currentChecklistId);
}
