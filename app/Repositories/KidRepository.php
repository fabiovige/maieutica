<?php

namespace App\Repositories;

use App\Models\Kid;

class KidRepository
{
    public function all()
    {
        return Kid::all();
    }

    public function find($id)
    {
        return Kid::find($id);
    }

    public function create(array $data)
    {
        return Kid::create($data);
    }

    public function update($id, array $data)
    {
        $kid = $this->find($id);
        $kid->update($data);
        return $kid;
    }

    public function delete($id)
    {
        $kid = $this->find($id);
        return $kid->delete();
    }
}
