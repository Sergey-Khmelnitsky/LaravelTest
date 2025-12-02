<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Common ingredients that will be shared
        $commonIngredients = [
            'Salt',
            'Pepper',
            'Olive Oil',
            'Butter',
            'Garlic',
            'Onion',
            'Tomato',
            'Carrot',
            'Potato',
            'Flour',
            'Sugar',
            'Egg',
            'Milk',
            'Cheese',
            'Chicken',
            'Beef',
            'Pork',
            'Fish',
            'Rice',
            'Pasta',
        ];

        // Assign ingredients to different users
        $userIndex = 0;
        foreach ($commonIngredients as $ingredientName) {
            Ingredient::create([
                'name' => $ingredientName,
                'user_id' => $users[$userIndex % $users->count()]->id,
            ]);
            $userIndex++;
        }

        // Additional user-specific ingredients
        $john = $users->where('email', 'john@example.com')->first();
        if ($john) {
            $johnIngredients = [
                'Basil',
                'Oregano',
                'Thyme',
                'Rosemary',
            ];
            foreach ($johnIngredients as $ingredientName) {
                Ingredient::create([
                    'name' => $ingredientName,
                    'user_id' => $john->id,
                ]);
            }
        }

        $jane = $users->where('email', 'jane@example.com')->first();
        if ($jane) {
            $janeIngredients = [
                'Cinnamon',
                'Nutmeg',
                'Vanilla Extract',
                'Chocolate',
            ];
            foreach ($janeIngredients as $ingredientName) {
                Ingredient::create([
                    'name' => $ingredientName,
                    'user_id' => $jane->id,
                ]);
            }
        }

        $totalIngredients = Ingredient::count();
        $this->command->info("Created {$totalIngredients} ingredients");
    }
}

