<?php

namespace Tests\Feature;

use App\Models\Cuisine;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CuisineTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test creating a new cuisine.
     */
    public function test_can_create_cuisine(): void
    {
        // Create a user for authentication
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Use a unique cuisine name that doesn't exist in seeders
        $cuisineName = 'Test Cuisine ' . time();

        // Authenticate the user
        $response = $this->actingAs($user, 'web')
            ->postJson('/api/cuisines', [
                'name' => $cuisineName,
            ]);

        // Assert the response
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Cuisine created successfully',
            ])
            ->assertJsonStructure([
                'message',
                'cuisine' => [
                    'id',
                    'name',
                    'user_id',
                    'created_at',
                    'updated_at',
                ],
            ]);

        // Assert the cuisine was created in the database
        $this->assertDatabaseHas('cuisines', [
            'name' => $cuisineName,
            'user_id' => $user->id,
        ]);

        // Assert the cuisine data in response
        $response->assertJson([
            'cuisine' => [
                'name' => $cuisineName,
                'user_id' => $user->id,
            ],
        ]);
    }

    /**
     * Test that unauthenticated users cannot create cuisines.
     */
    public function test_unauthenticated_user_cannot_create_cuisine(): void
    {
        // Use a unique cuisine name that doesn't exist in seeders
        $cuisineName = 'Unauthenticated Test Cuisine ' . time();

        $response = $this->postJson('/api/cuisines', [
            'name' => $cuisineName,
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Authentication required',
            ]);
    }

    /**
     * Test that cuisine name is required.
     */
    public function test_cuisine_name_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/cuisines', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test that cuisine name must be unique.
     * This test uses 'Italian' from seeders to verify uniqueness validation.
     */
    public function test_cuisine_name_must_be_unique(): void
    {
        $user = User::factory()->create();

        // Try to create a cuisine with a name that already exists in seeders
        // 'Italian' should exist from CuisineSeeder
        $response = $this->actingAs($user, 'web')
            ->postJson('/api/cuisines', [
                'name' => 'Italian',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}

