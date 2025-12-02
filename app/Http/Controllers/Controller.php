<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Recipe Book API",
 *     version="1.0.0",
 *     description="API for managing recipes, cuisines, ingredients, and user authentication",
 *     @OA\Contact(
 *         email="support@example.com"
 *     )
 * )
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="session",
 *     type="apiKey",
 *     in="cookie",
 *     name="laravel_session",
 *     description="Session-based authentication using Laravel sessions"
 * )
 *
 * @OA\Schema(
 *     schema="Recipe",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Carrot Soup"),
 *     @OA\Property(property="cuisine_id", type="integer", example=1),
 *     @OA\Property(property="description", type="string", example="A delicious soup"),
 *     @OA\Property(property="prep_time", type="integer", nullable=true, example=15),
 *     @OA\Property(property="cook_time", type="integer", nullable=true, example=30),
 *     @OA\Property(property="servings", type="integer", nullable=true, example=4),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="cuisine", ref="#/components/schemas/Cuisine"),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="steps", type="array", @OA\Items(ref="#/components/schemas/RecipeStep")),
 *     @OA\Property(property="attachment", type="array", @OA\Items(ref="#/components/schemas/Attachment"))
 * )
 *
 * @OA\Schema(
 *     schema="Cuisine",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Italian"),
 *     @OA\Property(property="user_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Ingredient",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Carrot"),
 *     @OA\Property(property="user_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="RecipeStep",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="recipe_id", type="integer", example=1),
 *     @OA\Property(property="step_number", type="integer", example=1),
 *     @OA\Property(property="description", type="string", example="Chop vegetables"),
 *     @OA\Property(property="order", type="integer", example=0),
 *     @OA\Property(property="ingredients", type="array", @OA\Items(ref="#/components/schemas/IngredientWithAmount"))
 * )
 *
 * @OA\Schema(
 *     schema="IngredientWithAmount",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Carrot"),
 *     @OA\Property(property="pivot", type="object",
 *         @OA\Property(property="amount", type="number", example=500),
 *         @OA\Property(property="unit", type="string", example="g")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Attachment",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="url", type="string", example="http://localhost/storage/attachments/image.jpg"),
 *     @OA\Property(property="name", type="string", example="image.jpg"),
 *     @OA\Property(property="original_name", type="string", example="photo.jpg"),
 *     @OA\Property(property="mime", type="string", example="image/jpeg"),
 *     @OA\Property(property="size", type="integer", example=102400)
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com")
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
