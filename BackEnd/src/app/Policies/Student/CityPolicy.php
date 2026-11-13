<?php

namespace App\Policies\Student;

use App\Models\City;
use App\Models\Student;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class CityPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Student $student): bool
    {   
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Student $student, City $role): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Student $student): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Student $student, City $role): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Student $student, City $role): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Student $student, City $role): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Student $student, City $role): bool
    {
        return false;
    }
}
