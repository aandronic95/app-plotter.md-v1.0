<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Build CSP directives
        $scriptSrc = ["'self'", "'unsafe-inline'", "https://fonts.bunny.net"];
        
        if ($this->allowUnsafeEval()) {
            $scriptSrc[] = "'unsafe-eval'";
        }
        
        $connectSrc = ["'self'", "ws:", "wss:"];
        $viteHost = $this->getViteHost();
        if ($viteHost) {
            $connectSrc[] = $viteHost;
        }
        
        $directives = [
            "default-src 'self'",
            "script-src " . implode(' ', $scriptSrc),
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
            "font-src 'self' https://fonts.bunny.net data:",
            "img-src 'self' data: blob: https:",
            "connect-src " . implode(' ', $connectSrc),
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
            // Removed upgrade-insecure-requests to allow both HTTP and HTTPS
        ];

        $csp = implode('; ', $directives);

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }

    /**
     * Allow unsafe-eval only in development (for Vite HMR)
     */
    private function allowUnsafeEval(): bool
    {
        return app()->environment('local', 'development');
    }

    /**
     * Get Vite dev server host for CSP
     */
    private function getViteHost(): string
    {
        if (app()->environment('local', 'development')) {
            // Vite default dev server runs on localhost:5173
            // You can override this via .env if needed
            $viteHost = env('VITE_HOST', 'localhost');
            $vitePort = env('VITE_PORT', '5173');
            return "http://{$viteHost}:{$vitePort}";
        }

        return '';
    }
}

