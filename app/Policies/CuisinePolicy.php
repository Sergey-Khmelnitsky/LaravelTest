<?php

namespace App\Policies;

use App\Models\Cuisine;
use App\Models\User;

class CuisinePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Cuisine $cuisine): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Разрешаем создание всем авторизованным пользователям
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Cuisine $cuisine): bool
    {
        // System cuisines (user_id is null) can only be updated by admins
        if ($cuisine->user_id === null) {
            return $this->isAdmin($user);
        }

        return $user->id === $cuisine->user_id || $this->isAdmin($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cuisine $cuisine): bool
    {
        // System cuisines (user_id is null) can only be deleted by admins
        if ($cuisine->user_id === null) {
            return $this->isAdmin($user);
        }

        return $user->id === $cuisine->user_id || $this->isAdmin($user);
    }

    /**
     * Check if user is admin.
     */
    protected function isAdmin(User $user): bool
    {
        $permissions = $user->permissions ?? [];
        return isset($permissions['platform.systems']) || isset($permissions['platform.systems.index']);
    }
}
