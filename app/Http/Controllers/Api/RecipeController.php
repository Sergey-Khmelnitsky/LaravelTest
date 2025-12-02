<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Recipes",
 *     description="Recipe management endpoints"
 * )
 */
class RecipeController extends Controller
{
    public function __construct()
    {
        // Middleware is already applied in routes/api.php via Route::middleware('web')
    }

    /**
     * Get list of recipes
     *
     * @OA\Get(
     *     path="/api/recipes",
     *     tags={"Recipes"},
     *     summary="Get list of recipes",
     *     security={{"session": {}}},
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Filter by recipe title (case-insensitive search)",
     *         required=false,
     *         @OA\Schema(type="string", example="Soup")
     *     ),
     *     @OA\Parameter(
     *         name="cuisine_id",
     *         in="query",
     *         description="Filter by cuisine ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of recipes",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Recipe")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        // Check authorization via Auth::guard('web')->user()
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        $this->authorize('viewAny', Recipe::class);

        $query = Recipe::with(['cuisine', 'user', 'steps.ingredients', 'attachment'])
            ->orderBy('created_at', 'desc');

        // Filter by title (search) - case insensitive using ILIKE for better Unicode support
        if ($request->has('title') && !empty($request->title)) {
            $query->whereRaw('title ILIKE ?', ['%' . $request->title . '%']);
        }

        // Filter by cuisine type
        if ($request->has('cuisine_id') && !empty($request->cuisine_id)) {
            $query->where('cuisine_id', $request->cuisine_id);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $recipes = $query->paginate($perPage);

        return response()->json($recipes);
    }

    /**
     * Create a new recipe
     *
     * @OA\Post(
     *     path="/api/recipes",
     *     tags={"Recipes"},
     *     summary="Create a new recipe",
     *     security={{"session": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "cuisine_id", "steps"},
     *             @OA\Property(property="title", type="string", example="Carrot Soup"),
     *             @OA\Property(property="cuisine_id", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="A delicious soup"),
     *             @OA\Property(property="prep_time", type="integer", example=15),
     *             @OA\Property(property="cook_time", type="integer", example=30),
     *             @OA\Property(property="servings", type="integer", example=4),
     *             @OA\Property(
     *                 property="steps",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="step_number", type="integer", example=1),
     *                     @OA\Property(property="order", type="integer", example=0),
     *                     @OA\Property(property="description", type="string", example="Chop vegetables"),
     *                     @OA\Property(
     *                         property="ingredients",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="ingredient_id", type="integer", example=1),
     *                             @OA\Property(property="amount", type="number", example=500),
     *                             @OA\Property(property="unit", type="string", example="g")
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="images", type="array", @OA\Items(type="integer"), example={1, 2})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Recipe created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Recipe created successfully"),
     *             @OA\Property(property="recipe", ref="#/components/schemas/Recipe")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreRecipeRequest $request): JsonResponse
    {
        $this->authorize('create', Recipe::class);

        try {
            DB::beginTransaction();

            // Create recipe
            $recipe = Recipe::create([
                'user_id' => $request->user()->id,
                'title' => $request->title,
                'cuisine_id' => $request->cuisine_id,
                'description' => $request->description,
                'prep_time' => $request->prep_time,
                'cook_time' => $request->cook_time,
                'servings' => $request->servings,
            ]);

            // Create steps with ingredients
            foreach ($request->steps as $stepData) {
                $step = $recipe->steps()->create([
                    'step_number' => $stepData['step_number'],
                    'description' => $stepData['description'],
                    'order' => $stepData['order'] ?? 0,
                ]);

                // Attach ingredients to step
                if (isset($stepData['ingredients']) && is_array($stepData['ingredients'])) {
                    foreach ($stepData['ingredients'] as $ingredientData) {
                        $step->ingredients()->attach($ingredientData['ingredient_id'], [
                            'amount' => $ingredientData['amount'] ?? null,
                            'unit' => $ingredientData['unit'] ?? null,
                        ]);
                    }
                }
            }

            // Attach images (attachments) via attachmentable table
            if ($request->has('images') && is_array($request->images)) {
                foreach ($request->images as $attachmentId) {
                    DB::table('attachmentable')->insert([
                        'attachmentable_type' => Recipe::class,
                        'attachmentable_id' => $recipe->id,
                        'attachment_id' => $attachmentId,
                    ]);
                }
            }

            DB::commit();

            $recipe->load(['cuisine', 'user', 'steps.ingredients', 'attachment']);

            return response()->json([
                'message' => 'Recipe created successfully',
                'recipe' => $recipe,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating recipe: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error creating recipe',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific recipe
     *
     * @OA\Get(
     *     path="/api/recipes/{id}",
     *     tags={"Recipes"},
     *     summary="Get a specific recipe",
     *     security={{"session": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recipe details",
     *         @OA\JsonContent(ref="#/components/schemas/Recipe")
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(Recipe $recipe): JsonResponse
    {
        // Check authorization via Auth::guard('web')->user()
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        $this->authorize('view', $recipe);

        $recipe->load(['cuisine', 'user', 'steps.ingredients', 'attachment']);

        return response()->json($recipe);
    }

    /**
     * Update a recipe
     *
     * @OA\Put(
     *     path="/api/recipes/{id}",
     *     tags={"Recipes"},
     *     summary="Update a recipe",
     *     security={{"session": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Recipe"),
     *             @OA\Property(property="cuisine_id", type="integer", example=1),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="prep_time", type="integer"),
     *             @OA\Property(property="cook_time", type="integer"),
     *             @OA\Property(property="servings", type="integer"),
     *             @OA\Property(property="steps", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="images", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recipe updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Recipe updated successfully"),
     *             @OA\Property(property="recipe", ref="#/components/schemas/Recipe")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateRecipeRequest $request, Recipe $recipe): JsonResponse
    {
        // Check authorization via Auth::guard('web')->user()
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        $this->authorize('update', $recipe);

        try {
            DB::beginTransaction();

            // Update main recipe fields
            $recipe->update($request->only([
                'title',
                'cuisine_id',
                'description',
                'prep_time',
                'cook_time',
                'servings',
            ]));

            // Update steps if provided
            if ($request->has('steps')) {
                // Delete old steps
                $recipe->steps()->delete();

                // Create new steps
                foreach ($request->steps as $stepData) {
                    $step = $recipe->steps()->create([
                        'step_number' => $stepData['step_number'],
                        'description' => $stepData['description'],
                        'order' => $stepData['order'] ?? 0,
                    ]);

                    // Attach ingredients to step
                    if (isset($stepData['ingredients']) && is_array($stepData['ingredients'])) {
                        foreach ($stepData['ingredients'] as $ingredientData) {
                            $step->ingredients()->attach($ingredientData['ingredient_id'], [
                                'amount' => $ingredientData['amount'] ?? null,
                                'unit' => $ingredientData['unit'] ?? null,
                            ]);
                        }
                    }
                }
            }

            // Update images if provided
            if ($request->has('images')) {
                // Delete old links
                DB::table('attachmentable')
                    ->where('attachmentable_type', Recipe::class)
                    ->where('attachmentable_id', $recipe->id)
                    ->delete();
                
                // Attach new images
                if (is_array($request->images)) {
                    foreach ($request->images as $attachmentId) {
                        DB::table('attachmentable')->insert([
                            'attachmentable_type' => Recipe::class,
                            'attachmentable_id' => $recipe->id,
                            'attachment_id' => $attachmentId,
                        ]);
                    }
                }
            }

            DB::commit();

            $recipe->load(['cuisine', 'user', 'steps.ingredients', 'attachment']);

            return response()->json([
                'message' => 'Recipe updated successfully',
                'recipe' => $recipe,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating recipe: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error updating recipe',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a recipe
     *
     * @OA\Delete(
     *     path="/api/recipes/{id}",
     *     tags={"Recipes"},
     *     summary="Delete a recipe",
     *     security={{"session": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recipe deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Recipe deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(Recipe $recipe): JsonResponse
    {
        $this->authorize('delete', $recipe);

        try {
            $recipe->delete();

            return response()->json([
                'message' => 'Recipe deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting recipe: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error deleting recipe',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
