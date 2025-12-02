<?php

namespace App\Policies;

use App\Models\Ingredient;
use App\Models\User;

class IngredientPolicy
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
    public function view(User $user, Ingredient $ingredient): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        // Разрешаем создание всем авторизованным пользователям
        return $user !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ingredient $ingredient): bool
    {
        // System ingredients (user_id is null) can only be updated by admins
        if ($ingredient->user_id === null) {
            return $this->isAdmin($user);
        }

        return $user->id === $ingredient->user_id || $this->isAdmin($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ingredient $ingredient): bool
    {
        // System ingredients (user_id is null) can only be deleted by admins
        if ($ingredient->user_id === null) {
            return $this->isAdmin($user);
        }

        // Check if ingredient is used in any recipe steps
        if ($ingredient->recipeSteps()->exists()) {
            return false; // Cannot delete if used in recipes
        }

        return $user->id === $ingredient->user_id || $this->isAdmin($user);
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
