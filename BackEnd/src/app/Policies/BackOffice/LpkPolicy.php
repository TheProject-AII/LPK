<?php

namespace App\Policies\BackOffice;

use App\Models\User;
use App\Models\Lpk;
use Illuminate\Auth\Access\Response;

class LpkPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('viewAny lpks');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Lpk $lpk): bool
    {
        return $user->hasPermissionTo('view lpks');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create lpks');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Lpk $lpk): bool
    {
        return $user->hasPermissionTo('update lpks');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Lpk $lpk): bool
    {
        return $user->hasPermissionTo('delete lpks');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Lpk $lpk): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Lpk $lpk): bool
    {
        return false;
    }
}
