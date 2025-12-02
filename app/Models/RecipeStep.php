<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RecipeStep extends Model
{
    protected $fillable = [
        'recipe_id',
        'step_number',
        'description',
        'order',
    ];

    protected $casts = [
        'step_number' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get the recipe that owns this step.
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Get the ingredients for this step with amounts.
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_step_ingredients')
                    ->withPivot('amount', 'unit')
                    ->withTimestamps()
                    ->orderBy('recipe_step_ingredients.id');
    }

    /**
     * Get ingredients with their amounts formatted.
     */
    public function getIngredientsWithAmountsAttribute()
    {
        return $this->ingredients->map(function ($ingredient) {
            return [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'amount' => $ingredient->pivot->amount,
                'unit' => $ingredient->pivot->unit,
            ];
        });
    }
}
