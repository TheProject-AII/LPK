<?php

namespace App\Policies\BackOffice;

use App\Models\User;
use App\Models\LpkCategory;
use Illuminate\Auth\Access\Response;

class LpkCategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('viewAny lpkCategories');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LpkCategory $LpkCategory): bool
    {
        return $user->hasPermissionTo('view lpkCategories');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create lpkCategories');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LpkCategory $LpkCategory): bool
    {
        return $user->hasPermissionTo('update lpkCategories');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LpkCategory $LpkCategory): bool
    {
        return $user->hasPermissionTo('delete lpkCategories');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LpkCategory $LpkCategory): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LpkCategory $LpkCategory): bool
    {
        return false;
    }
}
