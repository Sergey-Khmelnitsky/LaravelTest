<?php

namespace App\Policies;

use App\Models\Recipe;
use App\Models\User;

class RecipePolicy
{
    /**
     * Determine whether the user can view any models.
     * Users see only their recipes, admins see all (handled by Global Scope).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * Users can view their own recipes, admins can view all.
     */
    public function view(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id || $this->isAdmin($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id || $this->isAdmin($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id || $this->isAdmin($user);
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
