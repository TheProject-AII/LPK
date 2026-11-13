<?php

namespace App\Policies\BackOffice;

use App\Models\City;
use App\Models\User;
use App\Models\Student;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class CityPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {   
        return $user->hasPermissionTo('viewAny cities');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, City $role): bool
    {
        return $user->hasPermissionTo('view cities');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create cities');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, City $role): bool
    {
        return $user->hasPermissionTo('update cities');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, City $role): bool
    {
        return $user->hasPermissionTo('delete cities');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, City $role): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, City $role): bool
    {
        return false;
    }
}
