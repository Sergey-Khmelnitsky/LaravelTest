<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Cuisine extends Model
{
    protected $fillable = [
        'name',
        'user_id',
    ];

    /**
     * Get the user who created this cuisine.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the recipes for this cuisine.
     */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    /**
     * Scope a query to only include system cuisines.
     */
    public function scopeSystem(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope a query to only include user-created cuisines.
     */
    public function scopeUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to include system and user cuisines.
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
