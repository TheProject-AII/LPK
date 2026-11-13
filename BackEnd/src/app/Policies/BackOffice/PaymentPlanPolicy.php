<?php

namespace App\Policies\BackOffice;

use App\Models\PaymentPlan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentPlanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('viewAny paymentPlans');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PaymentPlan $role): bool
    {
        return $user->hasPermissionTo('view paymentPlans');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create paymentPlans');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PaymentPlan $role): bool
    {
        return $user->hasPermissionTo('update paymentPlans');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PaymentPlan $role): bool
    {
        return $user->hasPermissionTo('delete paymentPlans');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PaymentPlan $role): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PaymentPlan $role): bool
    {
        return false;
    }
}
