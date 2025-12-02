<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIngredientRequest;
use App\Models\Ingredient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Ingredients",
 *     description="Ingredient management endpoints"
 * )
 */
class IngredientController extends Controller
{
    public function __construct()
    {
        // Middleware is already applied in routes/api.php via Route::middleware('web')
    }

    /**
     * Get list of ingredients
     *
     * @OA\Get(
     *     path="/api/ingredients",
     *     tags={"Ingredients"},
     *     summary="Get list of ingredients",
     *     security={{"session": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of ingredients",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Ingredient")
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

        // Get all ingredients (available to all users)
        $ingredients = Ingredient::orderBy('name')
            ->get();

        return response()->json($ingredients);
    }

    /**
     * Create a new ingredient
     *
     * @OA\Post(
     *     path="/api/ingredients",
     *     tags={"Ingredients"},
     *     summary="Create a new ingredient",
     *     security={{"session": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Carrot", maxLength=255)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Ingredient created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ingredient created successfully"),
     *             @OA\Property(property="ingredient", ref="#/components/schemas/Ingredient")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreIngredientRequest $request): JsonResponse
    {
        // Check authorization via Auth::guard('web')->user()
        $user = Auth::guard('web')->user();
        
        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        $this->authorize('create', Ingredient::class);

        $ingredient = Ingredient::create([
            'name' => $request->name,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Ingredient created successfully',
            'ingredient' => $ingredient,
        ], 201);
    }

    /**
     * Get a specific ingredient
     *
     * @OA\Get(
     *     path="/api/ingredients/{id}",
     *     tags={"Ingredients"},
     *     summary="Get a specific ingredient",
     *     security={{"session": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ingredient details",
     *         @OA\JsonContent(ref="#/components/schemas/Ingredient")
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show(Ingredient $ingredient): JsonResponse
    {
        $this->authorize('view', $ingredient);

        return response()->json($ingredient);
    }

    /**
     * Update an ingredient
     *
     * @OA\Put(
     *     path="/api/ingredients/{id}",
     *     tags={"Ingredients"},
     *     summary="Update an ingredient",
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
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Carrot", maxLength=255)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ingredient updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ingredient updated successfully"),
     *             @OA\Property(property="ingredient", ref="#/components/schemas/Ingredient")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, Ingredient $ingredient): JsonResponse
    {
        $this->authorize('update', $ingredient);

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:ingredients,name,' . $ingredient->id],
        ]);

        $ingredient->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Ingredient updated successfully',
            'ingredient' => $ingredient,
        ]);
    }

    /**
     * Delete an ingredient
     *
     * @OA\Delete(
     *     path="/api/ingredients/{id}",
     *     tags={"Ingredients"},
     *     summary="Delete an ingredient",
     *     security={{"session": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ingredient deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ingredient deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Ingredient is used in recipes")
     * )
     */
    public function destroy(Ingredient $ingredient): JsonResponse
    {
        $this->authorize('delete', $ingredient);

        // Check if ingredient is used in recipes
        if ($ingredient->recipeSteps()->exists()) {
            return response()->json([
                'message' => 'Cannot delete ingredient as it is used in recipes',
            ], 422);
        }

        $ingredient->delete();

        return response()->json([
            'message' => 'Ingredient deleted successfully',
        ]);
    }
}
