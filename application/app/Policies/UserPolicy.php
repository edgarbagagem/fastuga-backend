<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;


    public function viewAny(User $user)
    {
        return $user->type == 'EM';
    }

    public function view(User $user, User $model)
    {
        return $user->type == "EM" || $user->id == $model->id;
    }
    public function update(User $user, User $model)
    {
        return $user->type == "EM" || $user->id == $model->id;
    }
    public function updatePassword(User $user, User $model)
    {
        return $user->id == $model->id;
    }

    //Managers create other employees accounts, customers create their own(no authorization needed)
    public function createEmployee(User $user)
    {
        return $user->type = "EM";
    }

    //Only managers can delete users
    public function delete(User $user)
    {
        return $user->type = "EM";
    }
}
