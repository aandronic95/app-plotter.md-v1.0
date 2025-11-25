<?php

use App\Http\Middleware\ContentSecurityPolicy;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            SetLocale::class,
            ContentSecurityPolicy::class,
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response, \Throwable $exception, Request $request) {
            // Handle ModelNotFoundException (e.g., from firstOrFail())
            if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Resursa nu a fost găsită.'], 404);
                }

                // Check if it's a product route
                $path = $request->path();
                if (str_starts_with($path, 'products/')) {
                    $slug = str_replace('products/', '', $path);
                    return Inertia::render('Errors/ProductNotFound', [
                        'status' => 404,
                        'slug' => $slug,
                    ])->toResponse($request)->setStatusCode(404);
                }
            }

            // Handle NotFoundHttpException
            if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Pagina nu a fost găsită.'], 404);
                }

                // Check if it's a product route
                $path = $request->path();
                if (str_starts_with($path, 'products/')) {
                    $slug = str_replace('products/', '', $path);
                    return Inertia::render('Errors/ProductNotFound', [
                        'status' => 404,
                        'slug' => $slug,
                    ])->toResponse($request)->setStatusCode(404);
                }

                return Inertia::render('Errors/NotFound', [
                    'status' => 404,
                ])->toResponse($request)->setStatusCode(404);
            }

            return $response;
        });
    })->create();
