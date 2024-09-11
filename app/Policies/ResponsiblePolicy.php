<?php

namespace App\Policies;

use App\Models\Responsible;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResponsiblePolicy
{
    use HandlesAuthorization;

    public function show(User $user, Responsible $responsible)
    {
        return $responsible->user_id === $user->id;
    }

    public function update(User $user, Responsible $responsible)
    {
        return $responsible->user_id === $user->id;
    }
}
