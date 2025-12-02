<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication endpoints"
 * )
 */
class ForgotPasswordController extends Controller
{
    protected $recaptchaService;

    public function __construct(RecaptchaService $recaptchaService)
    {
        $this->recaptchaService = $recaptchaService;
    }

    /**
     * Request password reset link
     *
     * @OA\Post(
     *     path="/api/password/email",
     *     tags={"Authentication"},
     *     summary="Request password reset link",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "recaptcha_token"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="recaptcha_token", type="string", example="recaptcha-token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset link sent or user not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="user_found", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password reset email sent to your email address.")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'recaptcha_token' => ['required', 'string'],
        ]);

        // Verify reCAPTCHA
        $recaptchaToken = $request->input('recaptcha_token');
        
        if (empty($recaptchaToken)) {
            throw ValidationException::withMessages([
                'recaptcha' => ['reCAPTCHA token not received. Please complete the reCAPTCHA verification.'],
            ]);
        }

        if (!$this->recaptchaService->verify($recaptchaToken, $request->ip())) {
            \Illuminate\Support\Facades\Log::warning('reCAPTCHA verification failed', [
                'email' => $request->email,
                'token_length' => strlen($recaptchaToken),
                'ip' => $request->ip(),
            ]);
            
            throw ValidationException::withMessages([
                'recaptcha' => ['reCAPTCHA verification failed. Please try again.'],
            ]);
        }

        // Check if user exists
        $user = \App\Models\User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'user_found' => false,
                'message' => 'User not found',
            ]);
        }

        // Send password reset email
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'user_found' => true,
                'message' => 'Password reset email sent to your email address.',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}
