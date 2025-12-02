<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\CuisineController;
use App\Http\Controllers\Api\IngredientController;
use App\Http\Controllers\Api\AttachmentController;

// Public routes (no auth required for registration/login)
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

// Authenticated routes
Route::middleware('web')->group(function () {
    Route::get('/user', [LoginController::class, 'user']);
    Route::post('/logout', [LoginController::class, 'logout']);

    // Password reset
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('/password/reset', [ResetPasswordController::class, 'reset']);

    // Recipes - require auth (checked in controller)
    Route::apiResource('recipes', RecipeController::class);

    // Cuisines - require auth (checked in controller)
    Route::apiResource('cuisines', CuisineController::class);

    // Ingredients - require auth (checked in controller)
    Route::apiResource('ingredients', IngredientController::class);

    // Attachments - require auth (checked in controller)
    Route::post('/attachments', [AttachmentController::class, 'store']);
    Route::get('/attachments/{attachment}', [AttachmentController::class, 'show']);
});

