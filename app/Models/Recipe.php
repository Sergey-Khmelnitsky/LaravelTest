<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Attachment\Attachable;
use Orchid\Attachment\Models\Attachment;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Recipe extends Model
{
    use Attachable, Filterable, AsSource;

    protected $fillable = [
        'user_id',
        'title',
        'cuisine_id',
        'description',
        'prep_time',
        'cook_time',
        'servings',
    ];

    protected $casts = [
        'prep_time' => 'integer',
        'cook_time' => 'integer',
        'servings' => 'integer',
    ];

    /**
     * Get the user who created this recipe.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cuisine for this recipe.
     */
    public function cuisine(): BelongsTo
    {
        return $this->belongsTo(Cuisine::class);
    }

    /**
     * Get the steps for this recipe.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(RecipeStep::class)->orderBy('order')->orderBy('step_number');
    }

    /**
     * Get the attachments (images) for this recipe.
     * Orchid uses attachmentable pivot table for polymorphic many-to-many relationship.
     */
    public function attachment()
    {
        return $this->morphToMany(
            Attachment::class,
            'attachmentable',
            'attachmentable',
            'attachmentable_id',
            'attachment_id'
        )->orderBy('sort');
    }

    /**
     * Get the main image (first image by sort order).
     */
    public function getMainImageAttribute()
    {
        return $this->attachment()->orderBy('sort')->first();
    }

    /**
     * Get total time (prep_time + cook_time).
     */
    public function getTotalTimeAttribute(): ?int
    {
        if ($this->prep_time === null && $this->cook_time === null) {
            return null;
        }

        return ($this->prep_time ?? 0) + ($this->cook_time ?? 0);
    }

    /**
     * Get all unique ingredients from all steps.
     */
    public function getAllIngredientsAttribute()
    {
        return $this->steps()
            ->with('ingredients')
            ->get()
            ->pluck('ingredients')
            ->flatten()
            ->unique('id')
            ->values();
    }

    /**
     * Get all images (attachments) for this recipe.
     */
    public function getImagesAttribute()
    {
        return $this->attachment()->orderBy('sort')->get();
    }

    /**
     * Global scope: users see only their recipes, admins see all.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('user_recipes', function (Builder $builder) {
            $user = auth()->guard('web')->user();
            
            if ($user && !self::isAdmin($user)) {
                $builder->where('user_id', $user->id);
            }
        });
    }

    /**
     * Check if user is admin.
     */
    protected static function isAdmin($user): bool
    {
        $permissions = $user->permissions ?? [];
        return isset($permissions['platform.systems']) || isset($permissions['platform.systems.index']);
    }
}
