<?php

namespace Database\Seeders;

use App\Models\Cuisine;
use App\Models\User;
use Illuminate\Database\Seeder;

class CuisineSeeder extends Seeder
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

        // Common cuisines that will be shared
        $commonCuisines = [
            'Italian',
            'French',
            'Chinese',
            'Japanese',
            'Mexican',
            'Indian',
            'Thai',
            'Mediterranean',
            'American',
            'Greek',
        ];

        // Assign cuisines to different users
        $userIndex = 0;
        foreach ($commonCuisines as $cuisineName) {
            Cuisine::create([
                'name' => $cuisineName,
                'user_id' => $users[$userIndex % $users->count()]->id,
            ]);
            $userIndex++;
        }

        // Additional user-specific cuisines
        $john = $users->where('email', 'john@example.com')->first();
        if ($john) {
            $johnCuisines = [
                'Spanish',
                'Turkish',
            ];
            foreach ($johnCuisines as $cuisineName) {
                Cuisine::create([
                    'name' => $cuisineName,
                    'user_id' => $john->id,
                ]);
            }
        }

        $jane = $users->where('email', 'jane@example.com')->first();
        if ($jane) {
            $janeCuisines = [
                'Korean',
                'Vietnamese',
            ];
            foreach ($janeCuisines as $cuisineName) {
                Cuisine::create([
                    'name' => $cuisineName,
                    'user_id' => $jane->id,
                ]);
            }
        }

        $totalCuisines = Cuisine::count();
        $this->command->info("Created {$totalCuisines} cuisines");
    }
}

