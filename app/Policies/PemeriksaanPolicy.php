<?php

namespace App\Policies;

use App\Models\Pemeriksaan;
use App\Models\User;

class PemeriksaanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isOperator();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Pemeriksaan $pemeriksaan): bool
    {
        return $user->isOperator();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isOperator();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pemeriksaan $pemeriksaan): bool
    {
        return $user->isOperator();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pemeriksaan $pemeriksaan): bool
    {
        return $user->isOperator();
    }

    /**
     * Determine whether the user can download PDF.
     */
    public function download(User $user, Pemeriksaan $pemeriksaan): bool
    {
        return $user->isOperator();
    }

    /**
     * Determine whether the user can preview PDF.
     */
    public function preview(User $user, Pemeriksaan $pemeriksaan): bool
    {
        return $user->isOperator();
    }

    /**
     * Determine whether the user can send email.
     */
    public function sendEmail(User $user, Pemeriksaan $pemeriksaan): bool
    {
        return $user->isOperator();
    }
}
