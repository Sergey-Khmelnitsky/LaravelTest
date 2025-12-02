<?php

namespace Database\Seeders;

use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeStep;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('email', '!=', 'admin@example.com')->get();
        $cuisines = Cuisine::all();
        $ingredients = Ingredient::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }
        
        if ($cuisines->isEmpty()) {
            $this->command->warn('No cuisines found. Please run CuisineSeeder first.');
            return;
        }
        
        if ($ingredients->isEmpty()) {
            $this->command->warn('No ingredients found. Please run IngredientSeeder first.');
            return;
        }

        // Recipe 1: Italian Pasta
        $john = $users->where('email', 'john@example.com')->first();
        $italian = $cuisines->where('name', 'Italian')->first();
        
        if ($john && $italian) {
            $pasta = Recipe::create([
                'user_id' => $john->id,
                'title' => 'Classic Spaghetti Carbonara',
                'cuisine_id' => $italian->id,
                'description' => 'A traditional Italian pasta dish with eggs, cheese, pancetta, and black pepper.',
                'prep_time' => 15,
                'cook_time' => 20,
                'servings' => 4,
            ]);

            // Step 1
            $step1 = RecipeStep::create([
                'recipe_id' => $pasta->id,
                'step_number' => 1,
                'order' => 0,
                'description' => 'Bring a large pot of salted water to a boil. Add spaghetti and cook according to package directions until al dente.',
            ]);
            $pastaIngredient = $ingredients->where('name', 'Pasta')->first();
            if ($pastaIngredient) {
                $step1->ingredients()->attach($pastaIngredient->id, [
                    'amount' => 400,
                    'unit' => 'g',
                ]);
            }

            // Step 2
            $step2 = RecipeStep::create([
                'recipe_id' => $pasta->id,
                'step_number' => 2,
                'order' => 1,
                'description' => 'In a large bowl, whisk together eggs, grated cheese, and black pepper.',
            ]);
            $egg = $ingredients->where('name', 'Egg')->first();
            $cheese = $ingredients->where('name', 'Cheese')->first();
            if ($egg) {
                $step2->ingredients()->attach($egg->id, [
                    'amount' => 4,
                    'unit' => 'pieces',
                ]);
            }
            if ($cheese) {
                $step2->ingredients()->attach($cheese->id, [
                    'amount' => 100,
                    'unit' => 'g',
                ]);
            }

            // Step 3
            RecipeStep::create([
                'recipe_id' => $pasta->id,
                'step_number' => 3,
                'order' => 2,
                'description' => 'Drain pasta and immediately toss with the egg mixture. Serve hot.',
            ]);
        }

        // Recipe 2: French Soup
        $jane = $users->where('email', 'jane@example.com')->first();
        $french = $cuisines->where('name', 'French')->first();
        
        if ($jane && $french) {
            $soup = Recipe::create([
                'user_id' => $jane->id,
                'title' => 'French Onion Soup',
                'cuisine_id' => $french->id,
                'description' => 'A rich and flavorful soup with caramelized onions, beef broth, and melted cheese.',
                'prep_time' => 20,
                'cook_time' => 60,
                'servings' => 6,
            ]);

            // Step 1
            $step1 = RecipeStep::create([
                'recipe_id' => $soup->id,
                'step_number' => 1,
                'order' => 0,
                'description' => 'Slice onions thinly and cook in butter over low heat until caramelized, about 30 minutes.',
            ]);
            $onion = $ingredients->where('name', 'Onion')->first();
            $butter = $ingredients->where('name', 'Butter')->first();
            if ($onion) {
                $step1->ingredients()->attach($onion->id, [
                    'amount' => 4,
                    'unit' => 'pieces',
                ]);
            }
            if ($butter) {
                $step1->ingredients()->attach($butter->id, [
                    'amount' => 50,
                    'unit' => 'g',
                ]);
            }

            // Step 2
            RecipeStep::create([
                'recipe_id' => $soup->id,
                'step_number' => 2,
                'order' => 1,
                'description' => 'Add beef broth and simmer for 20 minutes. Season with salt and pepper.',
            ]);

            // Step 3
            $step3 = RecipeStep::create([
                'recipe_id' => $soup->id,
                'step_number' => 3,
                'order' => 2,
                'description' => 'Ladle soup into bowls, top with bread and cheese, then broil until cheese is melted.',
            ]);
            $cheese = $ingredients->where('name', 'Cheese')->first();
            if ($cheese) {
                $step3->ingredients()->attach($cheese->id, [
                    'amount' => 150,
                    'unit' => 'g',
                ]);
            }
        }

        // Recipe 3: Chinese Stir Fry
        $bob = $users->where('email', 'bob@example.com')->first();
        $chinese = $cuisines->where('name', 'Chinese')->first();
        
        if ($bob && $chinese) {
            $stirFry = Recipe::create([
                'user_id' => $bob->id,
                'title' => 'Chicken Stir Fry',
                'cuisine_id' => $chinese->id,
                'description' => 'Quick and easy chicken stir fry with vegetables and soy sauce.',
                'prep_time' => 15,
                'cook_time' => 15,
                'servings' => 4,
            ]);

            // Step 1
            $step1 = RecipeStep::create([
                'recipe_id' => $stirFry->id,
                'step_number' => 1,
                'order' => 0,
                'description' => 'Cut chicken into bite-sized pieces and marinate with soy sauce for 10 minutes.',
            ]);
            $chicken = $ingredients->where('name', 'Chicken')->first();
            if ($chicken) {
                $step1->ingredients()->attach($chicken->id, [
                    'amount' => 500,
                    'unit' => 'g',
                ]);
            }

            // Step 2
            $step2 = RecipeStep::create([
                'recipe_id' => $stirFry->id,
                'step_number' => 2,
                'order' => 1,
                'description' => 'Heat oil in a wok and stir-fry chicken until cooked through.',
            ]);
            $oil = $ingredients->where('name', 'Olive Oil')->first();
            if ($oil) {
                $step2->ingredients()->attach($oil->id, [
                    'amount' => 30,
                    'unit' => 'ml',
                ]);
            }

            // Step 3
            $step3 = RecipeStep::create([
                'recipe_id' => $stirFry->id,
                'step_number' => 3,
                'order' => 2,
                'description' => 'Add vegetables and continue stir-frying for 5 minutes. Serve with rice.',
            ]);
            $carrot = $ingredients->where('name', 'Carrot')->first();
            $onion = $ingredients->where('name', 'Onion')->first();
            $rice = $ingredients->where('name', 'Rice')->first();
            if ($carrot) {
                $step3->ingredients()->attach($carrot->id, [
                    'amount' => 2,
                    'unit' => 'pieces',
                ]);
            }
            if ($onion) {
                $step3->ingredients()->attach($onion->id, [
                    'amount' => 1,
                    'unit' => 'piece',
                ]);
            }
            if ($rice) {
                $step3->ingredients()->attach($rice->id, [
                    'amount' => 300,
                    'unit' => 'g',
                ]);
            }
        }

        // Recipe 4: Simple Salad (John)
        if ($john && $italian) {
            $salad = Recipe::create([
                'user_id' => $john->id,
                'title' => 'Fresh Garden Salad',
                'cuisine_id' => $italian->id,
                'description' => 'A simple and healthy salad with fresh vegetables.',
                'prep_time' => 10,
                'cook_time' => 0,
                'servings' => 2,
            ]);

            // Step 1
            $step1 = RecipeStep::create([
                'recipe_id' => $salad->id,
                'step_number' => 1,
                'order' => 0,
                'description' => 'Wash and chop all vegetables into bite-sized pieces.',
            ]);
            $tomato = $ingredients->where('name', 'Tomato')->first();
            $carrot = $ingredients->where('name', 'Carrot')->first();
            if ($tomato) {
                $step1->ingredients()->attach($tomato->id, [
                    'amount' => 2,
                    'unit' => 'pieces',
                ]);
            }
            if ($carrot) {
                $step1->ingredients()->attach($carrot->id, [
                    'amount' => 1,
                    'unit' => 'piece',
                ]);
            }

            // Step 2
            $step2 = RecipeStep::create([
                'recipe_id' => $salad->id,
                'step_number' => 2,
                'order' => 1,
                'description' => 'Drizzle with olive oil and season with salt and pepper. Toss gently.',
            ]);
            $oil = $ingredients->where('name', 'Olive Oil')->first();
            $salt = $ingredients->where('name', 'Salt')->first();
            $pepper = $ingredients->where('name', 'Pepper')->first();
            if ($oil) {
                $step2->ingredients()->attach($oil->id, [
                    'amount' => 20,
                    'unit' => 'ml',
                ]);
            }
            if ($salt) {
                $step2->ingredients()->attach($salt->id, [
                    'amount' => 1,
                    'unit' => 'pinch',
                ]);
            }
            if ($pepper) {
                $step2->ingredients()->attach($pepper->id, [
                    'amount' => 1,
                    'unit' => 'pinch',
                ]);
            }
        }

        $totalRecipes = Recipe::count();
        $totalSteps = RecipeStep::count();
        $this->command->info("Created {$totalRecipes} recipes with {$totalSteps} steps");
    }
}

