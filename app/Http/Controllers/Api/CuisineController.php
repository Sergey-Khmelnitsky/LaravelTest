<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCuisineRequest;
use App\Models\Cuisine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Cuisines",
 *     description="Cuisine management endpoints"
 * )
 */
class CuisineController extends Controller
{
    public function __construct()
    {
        // Middleware is already applied in routes/api.php via Route::middleware('web')
        // But can be added here for additional security
    }

    /**
     * Get list of cuisines
     *
     * @OA\Get(
     *     path="/api/cuisines",
     *     tags={"Cuisines"},
     *     summary="Get list of cuisines",
     *     security={{"session": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of cuisines",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Cuisine")
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

        // Get all cuisines (available to all users)
        $cuisines = Cuisine::orderBy('name')
            ->get();

        return response()->json($cuisines);
    }

    /**
     * Create a new cuisine
     *
     * @OA\Post(
     *     path="/api/cuisines",
     *     tags={"Cuisines"},
     *     summary="Create a new cuisine",
     *     security={{"session": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Italian", maxLength=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Cuisine created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cuisine created successfully"),
     *             @OA\Property(property="cuisine", ref="#/components/schemas/Cuisine")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreCuisineRequest $request): JsonResponse
    {
        // Check authorization via Auth::guard('web')->user()
        $user = Auth::guard('web')->user();
        
        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        $this->authorize('create', Cuisine::class);

        $cuisine = Cuisine::create([
            'name' => $request->name,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Cuisine created successfully',
            'cuisine' => $cuisine,
        ], 201);
    }

    /**
     * Get a specific cuisine
     *
     * @OA\Get(
     *     path="/api/cuisines/{id}",
     *     tags={"Cuisines"},
     *     summary="Get a specific cuisine",
     *     security={{"session": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cuisine details",
     *         @OA\JsonContent(ref="#/components/schemas/Cuisine")
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show(Cuisine $cuisine): JsonResponse
    {
        $this->authorize('view', $cuisine);

        return response()->json($cuisine);
    }

    /**
     * Update a cuisine
     *
     * @OA\Put(
     *     path="/api/cuisines/{id}",
     *     tags={"Cuisines"},
     *     summary="Update a cuisine",
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
     *             @OA\Property(property="name", type="string", example="Italian", maxLength=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cuisine updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cuisine updated successfully"),
     *             @OA\Property(property="cuisine", ref="#/components/schemas/Cuisine")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, Cuisine $cuisine): JsonResponse
    {
        $this->authorize('update', $cuisine);

        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:cuisines,name,' . $cuisine->id],
        ]);

        $cuisine->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Cuisine updated successfully',
            'cuisine' => $cuisine,
        ]);
    }

    /**
     * Delete a cuisine
     *
     * @OA\Delete(
     *     path="/api/cuisines/{id}",
     *     tags={"Cuisines"},
     *     summary="Delete a cuisine",
     *     security={{"session": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cuisine deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cuisine deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Cuisine is used in recipes")
     * )
     */
    public function destroy(Cuisine $cuisine): JsonResponse
    {
        $this->authorize('delete', $cuisine);

        // Check if cuisine is used in recipes
        if ($cuisine->recipes()->exists()) {
            return response()->json([
                'message' => 'Cannot delete cuisine as it is used in recipes',
            ], 422);
        }

        $cuisine->delete();

        return response()->json([
            'message' => 'Cuisine deleted successfully',
        ]);
    }
}
