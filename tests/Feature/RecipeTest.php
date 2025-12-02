<?php

namespace Tests\Feature;

use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Orchid\Attachment\Models\Attachment;
use Tests\TestCase;

class RecipeTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test creating a recipe with steps and images.
     */
    public function test_can_create_recipe_with_steps_and_images(): void
    {
        // Create a user for authentication
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test-recipe@example.com',
            'password' => bcrypt('password'),
        ]);

        // Use existing cuisine from seeders (Italian)
        $cuisine = Cuisine::where('name', 'Italian')->first();
        if (!$cuisine) {
            $cuisine = Cuisine::create([
                'name' => 'Italian',
                'user_id' => $user->id,
            ]);
        }

        // Use existing ingredients from seeders
        $carrot = Ingredient::where('name', 'carrot')->first();
        if (!$carrot) {
            $carrot = Ingredient::create([
                'name' => 'carrot',
                'user_id' => $user->id,
            ]);
        }

        $onion = Ingredient::where('name', 'onion')->first();
        if (!$onion) {
            $onion = Ingredient::create([
                'name' => 'onion',
                'user_id' => $user->id,
            ]);
        }

        // Create fake image attachments
        Storage::fake('public');
        
        // Create fake image files using text files (simpler for testing)
        $image1 = UploadedFile::fake()->create('recipe1.jpg', 100, 'image/jpeg');
        $image2 = UploadedFile::fake()->create('recipe2.jpg', 100, 'image/jpeg');

        // Upload images and get attachment IDs
        $attachment1 = $this->actingAs($user, 'web')
            ->postJson('/api/attachments', [
                'file' => $image1,
            ])
            ->assertStatus(201)
            ->json();

        $attachment2 = $this->actingAs($user, 'web')
            ->postJson('/api/attachments', [
                'file' => $image2,
            ])
            ->assertStatus(201)
            ->json();

        // Create recipe with steps and images
        $response = $this->actingAs($user, 'web')
            ->postJson('/api/recipes', [
                'title' => 'Carrot Soup',
                'cuisine_id' => $cuisine->id,
                'description' => 'A delicious carrot soup recipe',
                'prep_time' => 15,
                'cook_time' => 30,
                'servings' => 4,
                'steps' => [
                    [
                        'step_number' => 1,
                        'order' => 0,
                        'description' => 'Chop the carrots and onions',
                        'ingredients' => [
                            [
                                'ingredient_id' => $carrot->id,
                                'amount' => 500,
                                'unit' => 'g',
                            ],
                            [
                                'ingredient_id' => $onion->id,
                                'amount' => 1,
                                'unit' => 'piece',
                            ],
                        ],
                    ],
                    [
                        'step_number' => 2,
                        'order' => 1,
                        'description' => 'Cook the vegetables in a pot',
                        'ingredients' => [],
                    ],
                ],
                'images' => [
                    $attachment1['id'],
                    $attachment2['id'],
                ],
            ]);

        // Assert the response
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Recipe created successfully',
            ])
            ->assertJsonStructure([
                'message',
                'recipe' => [
                    'id',
                    'title',
                    'cuisine_id',
                    'description',
                    'prep_time',
                    'cook_time',
                    'servings',
                    'user_id',
                    'cuisine',
                    'user',
                    'steps',
                    'attachment',
                    'created_at',
                    'updated_at',
                ],
            ]);

        // Assert the recipe was created in the database
        $this->assertDatabaseHas('recipes', [
            'title' => 'Carrot Soup',
            'cuisine_id' => $cuisine->id,
            'user_id' => $user->id,
            'description' => 'A delicious carrot soup recipe',
            'prep_time' => 15,
            'cook_time' => 30,
            'servings' => 4,
        ]);

        // Get the created recipe
        $recipe = Recipe::where('title', 'Carrot Soup')->first();

        // Assert steps were created
        $this->assertDatabaseHas('recipe_steps', [
            'recipe_id' => $recipe->id,
            'step_number' => 1,
            'description' => 'Chop the carrots and onions',
            'order' => 0,
        ]);

        $this->assertDatabaseHas('recipe_steps', [
            'recipe_id' => $recipe->id,
            'step_number' => 2,
            'description' => 'Cook the vegetables in a pot',
            'order' => 1,
        ]);

        // Assert ingredients were attached to steps
        $step1 = $recipe->steps()->where('step_number', 1)->first();
        $this->assertTrue($step1->ingredients->contains($carrot));
        $this->assertTrue($step1->ingredients->contains($onion));

        // Assert images were attached
        $this->assertDatabaseHas('attachmentable', [
            'attachmentable_type' => Recipe::class,
            'attachmentable_id' => $recipe->id,
            'attachment_id' => $attachment1['id'],
        ]);

        $this->assertDatabaseHas('attachmentable', [
            'attachmentable_type' => Recipe::class,
            'attachmentable_id' => $recipe->id,
            'attachment_id' => $attachment2['id'],
        ]);

        // Assert the recipe data in response
        $response->assertJson([
            'recipe' => [
                'title' => 'Carrot Soup',
                'cuisine_id' => $cuisine->id,
                'user_id' => $user->id,
                'description' => 'A delicious carrot soup recipe',
                'prep_time' => 15,
                'cook_time' => 30,
                'servings' => 4,
            ],
        ]);

        // Assert steps in response
        $responseData = $response->json('recipe');
        $this->assertCount(2, $responseData['steps']);
        $this->assertEquals('Chop the carrots and onions', $responseData['steps'][0]['description']);
        $this->assertEquals('Cook the vegetables in a pot', $responseData['steps'][1]['description']);
    }

    /**
     * Test that unauthenticated users cannot create recipes.
     */
    public function test_unauthenticated_user_cannot_create_recipe(): void
    {
        // Use existing cuisine from seeders
        $cuisine = Cuisine::where('name', 'Italian')->first();
        if (!$cuisine) {
            $user = User::factory()->create();
            $cuisine = Cuisine::create([
                'name' => 'Italian',
                'user_id' => $user->id,
            ]);
        }
        
        $response = $this->postJson('/api/recipes', [
            'title' => 'Test Recipe',
            'cuisine_id' => $cuisine->id,
            'steps' => [
                [
                    'step_number' => 1,
                    'description' => 'Test step',
                ],
            ],
        ]);

        // Authorization check happens after validation, so we get 403 Forbidden
        // instead of 401 Unauthorized when user is not authenticated
        $response->assertStatus(403);
    }

    /**
     * Test that recipe title is required.
     */
    public function test_recipe_title_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/recipes', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }
}

