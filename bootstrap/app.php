<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        ]);
        
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Redirect to admin login instead of showing 403 for unauthorized admin access
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('admin*') || $request->is('admin/*')) {
                // If user is authenticated but doesn't have permissions, logout and redirect to login
                if (auth()->guard('web')->check()) {
                    auth()->guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }
                return redirect()->route('platform.login')->withErrors([
                    'email' => 'You do not have permission to access the admin panel. Please login with an admin account.'
                ]);
            }
            return null; // Let Laravel handle it normally for non-admin routes
        });
        
        // Also handle 403 HttpException for admin routes
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() === 403 && ($request->is('admin*') || $request->is('admin/*'))) {
                // If user is authenticated but doesn't have permissions, logout and redirect to login
                if (auth()->guard('web')->check()) {
                    auth()->guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }
                return redirect()->route('platform.login')->withErrors([
                    'email' => 'You do not have permission to access the admin panel. Please login with an admin account.'
                ]);
            }
            return null; // Let Laravel handle it normally for non-admin routes
        });
    })->create();
