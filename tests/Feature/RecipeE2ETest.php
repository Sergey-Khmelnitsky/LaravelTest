<?php

namespace Tests\Feature;

use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecipeE2ETest extends TestCase
{
    use DatabaseTransactions;

    /**
     * End-to-end test: Complete recipe creation flow
     * 
     * This test simulates a complete user journey:
     * 1. User registration
     * 2. User login
     * 3. Create cuisine (Italian)
     * 4. Create ingredients (carrot, onion)
     * 5. Upload images
     * 6. Create recipe with steps and images
     * 7. View recipe list
     * 8. View specific recipe
     */
    public function test_complete_recipe_creation_flow(): void
    {
        Storage::fake('public');

        // Step 1: Register a new user (use unique email)
        $uniqueEmail = 'e2e-test-' . time() . '@example.com';
        $registerResponse = $this->postJson('/api/register', [
            'name' => 'E2E Test User',
            'email' => $uniqueEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $registerResponse->assertStatus(201)
            ->assertJson([
                'message' => 'Registration successful',
            ]);

        $user = User::where('email', $uniqueEmail)->first();
        $this->assertNotNull($user);
        $this->assertEquals('E2E Test User', $user->name);

        // Step 2: Login (user is auto-logged in after registration, but let's test login explicitly)
        $loginResponse = $this->postJson('/api/login', [
            'email' => $uniqueEmail,
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ]);

        // Step 3: Use existing cuisine from seeders (Italian)
        $cuisine = Cuisine::where('name', 'Italian')->first();
        $this->assertNotNull($cuisine, 'Italian cuisine should exist from seeders');

        // Step 4: Use existing ingredients from seeders (case-insensitive search)
        $carrot = Ingredient::whereRaw('LOWER(name) = ?', ['carrot'])->first();
        $onion = Ingredient::whereRaw('LOWER(name) = ?', ['onion'])->first();
        if (!$carrot) {
            // Create if doesn't exist
            $carrot = Ingredient::create([
                'name' => 'Carrot',
                'user_id' => $user->id,
            ]);
        }
        if (!$onion) {
            // Create if doesn't exist
            $onion = Ingredient::create([
                'name' => 'Onion',
                'user_id' => $user->id,
            ]);
        }

        // Step 5: Upload images
        $image1 = UploadedFile::fake()->create('recipe1.jpg', 100, 'image/jpeg');
        $image2 = UploadedFile::fake()->create('recipe2.jpg', 100, 'image/jpeg');

        $attachment1Response = $this->actingAs($user, 'web')
            ->postJson('/api/attachments', [
                'file' => $image1,
            ]);

        $attachment1Response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'url',
                'name',
                'original_name',
                'mime',
                'size',
            ]);

        $attachment2Response = $this->actingAs($user, 'web')
            ->postJson('/api/attachments', [
                'file' => $image2,
            ]);

        $attachment2Response->assertStatus(201);

        $attachment1Id = $attachment1Response->json('id');
        $attachment2Id = $attachment2Response->json('id');

        // Step 6: Create recipe with steps and images
        $recipeResponse = $this->actingAs($user, 'web')
            ->postJson('/api/recipes', [
                'title' => 'Carrot Soup',
                'cuisine_id' => $cuisine->id,
                'description' => 'A delicious Italian carrot soup recipe',
                'prep_time' => 15,
                'cook_time' => 30,
                'servings' => 4,
                'steps' => [
                    [
                        'step_number' => 1,
                        'order' => 0,
                        'description' => 'Chop the carrots and onions into small pieces',
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
                        'description' => 'Cook the vegetables in a pot with water for 30 minutes',
                        'ingredients' => [],
                    ],
                    [
                        'step_number' => 3,
                        'order' => 2,
                        'description' => 'Blend the soup until smooth',
                        'ingredients' => [],
                    ],
                ],
                'images' => [
                    $attachment1Id,
                    $attachment2Id,
                ],
            ]);

        $recipeResponse->assertStatus(201)
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
                ],
            ]);

        $recipe = Recipe::where('title', 'Carrot Soup')->first();
        $this->assertNotNull($recipe);
        $this->assertEquals('A delicious Italian carrot soup recipe', $recipe->description);
        $this->assertEquals(15, $recipe->prep_time);
        $this->assertEquals(30, $recipe->cook_time);
        $this->assertEquals(4, $recipe->servings);

        // Verify steps were created
        $this->assertCount(3, $recipe->steps);
        $this->assertEquals('Chop the carrots and onions into small pieces', $recipe->steps[0]->description);
        $this->assertEquals('Cook the vegetables in a pot with water for 30 minutes', $recipe->steps[1]->description);
        $this->assertEquals('Blend the soup until smooth', $recipe->steps[2]->description);

        // Verify ingredients are attached to first step
        $step1 = $recipe->steps()->where('step_number', 1)->first();
        $this->assertTrue($step1->ingredients->contains($carrot));
        $this->assertTrue($step1->ingredients->contains($onion));
        $this->assertEquals(500, $step1->ingredients->find($carrot->id)->pivot->amount);
        $this->assertEquals('g', $step1->ingredients->find($carrot->id)->pivot->unit);

        // Verify images are attached
        $this->assertDatabaseHas('attachmentable', [
            'attachmentable_type' => Recipe::class,
            'attachmentable_id' => $recipe->id,
            'attachment_id' => $attachment1Id,
        ]);

        $this->assertDatabaseHas('attachmentable', [
            'attachmentable_type' => Recipe::class,
            'attachmentable_id' => $recipe->id,
            'attachment_id' => $attachment2Id,
        ]);

        // Step 7: View recipe list
        $listResponse = $this->actingAs($user, 'web')
            ->getJson('/api/recipes');

        $listResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'cuisine_id',
                        'description',
                        'prep_time',
                        'cook_time',
                        'servings',
                    ],
                ],
            ]);

        $listData = $listResponse->json('data');
        $this->assertCount(1, $listData);
        $this->assertEquals('Carrot Soup', $listData[0]['title']);

        // Step 8: View specific recipe
        $showResponse = $this->actingAs($user, 'web')
            ->getJson("/api/recipes/{$recipe->id}");

        $showResponse->assertStatus(200)
            ->assertJson([
                'id' => $recipe->id,
                'title' => 'Carrot Soup',
                'description' => 'A delicious Italian carrot soup recipe',
            ])
            ->assertJsonStructure([
                'id',
                'title',
                'cuisine_id',
                'description',
                'prep_time',
                'cook_time',
                'servings',
                'cuisine',
                'user',
                'steps',
                'attachment',
            ]);

        $showData = $showResponse->json();
        $this->assertCount(3, $showData['steps']);
        $this->assertCount(2, $showData['attachment']);
    }

    /**
     * End-to-end test: User can filter recipes by title and cuisine
     */
    public function test_user_can_filter_recipes(): void
    {
        $user = User::factory()->create();

        // Use existing cuisines from seeders
        $italian = Cuisine::where('name', 'Italian')->first();
        $french = Cuisine::where('name', 'French')->first();
        
        $this->assertNotNull($italian, 'Italian cuisine should exist from seeders');
        $this->assertNotNull($french, 'French cuisine should exist from seeders');

        // Create recipes
        $recipe1 = Recipe::create([
            'user_id' => $user->id,
            'title' => 'Carrot Soup',
            'cuisine_id' => $italian->id,
            'description' => 'Italian carrot soup',
        ]);

        $recipe2 = Recipe::create([
            'user_id' => $user->id,
            'title' => 'Borscht',
            'cuisine_id' => $french->id,
            'description' => 'French borscht',
        ]);

        // Filter by title
        $filteredResponse = $this->actingAs($user, 'web')
            ->getJson('/api/recipes?title=Carrot');

        $filteredResponse->assertStatus(200);
        $filteredData = $filteredResponse->json('data');
        $this->assertCount(1, $filteredData);
        $this->assertEquals('Carrot Soup', $filteredData[0]['title']);

        // Filter by cuisine
        $cuisineFilteredResponse = $this->actingAs($user, 'web')
            ->getJson("/api/recipes?cuisine_id={$italian->id}");

        $cuisineFilteredResponse->assertStatus(200);
        $cuisineFilteredData = $cuisineFilteredResponse->json('data');
        $this->assertCount(1, $cuisineFilteredData);
        $this->assertEquals('Carrot Soup', $cuisineFilteredData[0]['title']);
        $this->assertEquals($italian->id, $cuisineFilteredData[0]['cuisine_id']);

        // Filter by both title and cuisine
        $bothFilteredResponse = $this->actingAs($user, 'web')
            ->getJson("/api/recipes?title=Borscht&cuisine_id={$french->id}");

        $bothFilteredResponse->assertStatus(200);
        $bothFilteredData = $bothFilteredResponse->json('data');
        $this->assertCount(1, $bothFilteredData);
        $this->assertEquals('Borscht', $bothFilteredData[0]['title']);
    }
}

