<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Orchid\Attachment\File;
use Orchid\Attachment\Models\Attachment;

/**
 * @OA\Tag(
 *     name="Attachments",
 *     description="File upload endpoints"
 * )
 */
class AttachmentController extends Controller
{
    // Middleware is already applied in routes/api.php via Route::middleware(['web', 'auth:web'])

    /**
     * Upload a file
     *
     * @OA\Post(
     *     path="/api/attachments",
     *     tags={"Attachments"},
     *     summary="Upload a file (image)",
     *     security={{"session": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="Image file (max 10MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="File uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="url", type="string", example="http://localhost/storage/attachments/image.jpg"),
     *             @OA\Property(property="name", type="string", example="image.jpg"),
     *             @OA\Property(property="original_name", type="string", example="photo.jpg"),
     *             @OA\Property(property="mime", type="string", example="image/jpeg"),
     *             @OA\Property(property="size", type="integer", example=102400)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        // Check authorization via Auth::guard('web')->user()
        $user = \Illuminate\Support\Facades\Auth::guard('web')->user();
        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        $request->validate([
            'file' => ['required', 'file', 'max:10240'], // 10MB max
        ]);

        try {
            $file = new File($request->file('file'), 'public');
            $attachment = $file->load();

            return response()->json([
                'id' => $attachment->id,
                'url' => $attachment->url,
                'name' => $attachment->name,
                'original_name' => $attachment->original_name,
                'mime' => $attachment->mime,
                'size' => $attachment->size,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error uploading file',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get attachment by ID
     *
     * @OA\Get(
     *     path="/api/attachments/{id}",
     *     tags={"Attachments"},
     *     summary="Get attachment by ID",
     *     security={{"session": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attachment details",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="url", type="string", example="http://localhost/storage/attachments/image.jpg"),
     *             @OA\Property(property="name", type="string", example="image.jpg"),
     *             @OA\Property(property="original_name", type="string", example="photo.jpg"),
     *             @OA\Property(property="mime", type="string", example="image/jpeg"),
     *             @OA\Property(property="size", type="integer", example=102400)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show(Attachment $attachment): JsonResponse
    {
        return response()->json([
            'id' => $attachment->id,
            'url' => $attachment->url,
            'name' => $attachment->name,
            'original_name' => $attachment->original_name,
            'mime' => $attachment->mime,
            'size' => $attachment->size,
        ]);
    }
}

