<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class IngredientTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test creating a new ingredient (carrot).
     */
    public function test_can_create_carrot_ingredient(): void
    {
        // Create a user for authentication
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Authenticate the user
        $response = $this->actingAs($user, 'web')
            ->postJson('/api/ingredients', [
                'name' => 'carrot',
            ]);

        // Assert the response
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Ingredient created successfully',
            ])
            ->assertJsonStructure([
                'message',
                'ingredient' => [
                    'id',
                    'name',
                    'user_id',
                    'created_at',
                    'updated_at',
                ],
            ]);

        // Assert the ingredient was created in the database
        $this->assertDatabaseHas('ingredients', [
            'name' => 'carrot',
            'user_id' => $user->id,
        ]);

        // Assert the ingredient data in response
        $response->assertJson([
            'ingredient' => [
                'name' => 'carrot',
                'user_id' => $user->id,
            ],
        ]);
    }

    /**
     * Test that unauthenticated users cannot create ingredients.
     */
    public function test_unauthenticated_user_cannot_create_ingredient(): void
    {
        $response = $this->postJson('/api/ingredients', [
            'name' => 'carrot',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Authentication required',
            ]);
    }

    /**
     * Test that ingredient name is required.
     */
    public function test_ingredient_name_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/ingredients', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test that ingredient name must be unique.
     */
    public function test_ingredient_name_must_be_unique(): void
    {
        $user = User::factory()->create();

        // Create first ingredient
        Ingredient::create([
            'name' => 'carrot',
            'user_id' => $user->id,
        ]);

        // Try to create duplicate
        $response = $this->actingAs($user, 'web')
            ->postJson('/api/ingredients', [
                'name' => 'carrot',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}

