<?php

namespace App\Policies\BackOffice;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('viewAny payments');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Payment $role): bool
    {
        return $user->hasPermissionTo('view payments');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create payments');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Payment $role): bool
    {
        return $user->hasPermissionTo('update payments');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Payment $role): bool
    {
        return $user->hasPermissionTo('delete payments');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Payment $role): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Payment $role): bool
    {
        return false;
    }
}
