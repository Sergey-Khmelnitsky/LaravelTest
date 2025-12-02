<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * 
     * Seeds are run in the following order:
     * 1. Users (including admin)
     * 2. Ingredients (depend on users)
     * 3. Cuisines (depend on users)
     * 4. Recipes (depend on users, cuisines, and ingredients)
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            IngredientSeeder::class,
            CuisineSeeder::class,
            RecipeSeeder::class,
        ]);
    }
}
