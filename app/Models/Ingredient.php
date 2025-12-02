<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'user_id',
    ];

    /**
     * Get the user who created this ingredient.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the recipe steps that use this ingredient.
     */
    public function recipeSteps(): BelongsToMany
    {
        return $this->belongsToMany(RecipeStep::class, 'recipe_step_ingredients')
                    ->withPivot('amount', 'unit')
                    ->withTimestamps();
    }

    /**
     * Scope a query to only include system ingredients.
     */
    public function scopeSystem(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope a query to only include user-created ingredients.
     */
    public function scopeUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to include system and user ingredients.
     */
    public function scopeAvailableForUser(Builder $query, ?int $userId = null): Builder
    {
        if ($userId === null) {
            return $query->system();
        }

        return $query->where(function ($q) use ($userId) {
            $q->whereNull('user_id')
              ->orWhere('user_id', $userId);
        });
    }
}
