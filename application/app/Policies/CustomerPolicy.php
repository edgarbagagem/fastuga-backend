<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Customer $model)
    {
        return $user->type == "EM" || $user->id == $model->user_id;
    }
}
