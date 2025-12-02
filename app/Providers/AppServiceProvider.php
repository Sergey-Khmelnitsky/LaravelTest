<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use App\Models\Recipe;
use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Policies\RecipePolicy;
use App\Policies\CuisinePolicy;
use App\Policies\IngredientPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Recipe::class => RecipePolicy::class,
        Cuisine::class => CuisinePolicy::class,
        Ingredient::class => IngredientPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        // Register policies explicitly
        Gate::policy(Recipe::class, RecipePolicy::class);
        Gate::policy(Cuisine::class, CuisinePolicy::class);
        Gate::policy(Ingredient::class, IngredientPolicy::class);
    }
}
