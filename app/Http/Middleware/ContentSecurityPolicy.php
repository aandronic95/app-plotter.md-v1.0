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
        $viteHosts = $this->getViteHosts();
        if (!empty($viteHosts)) {
            $scriptSrc = array_merge($scriptSrc, $viteHosts);
            $connectSrc = array_merge($connectSrc, $viteHosts);
        }
        
        $directives = [
            "default-src 'self'",
            "script-src " . implode(' ', $scriptSrc),
            "script-src-elem " . implode(' ', $scriptSrc),
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
        
        // Prevent caching of CSP headers in development to avoid stale policies
        if ($this->isDevelopment()) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }

    /**
     * Allow unsafe-eval only in development (for Vite HMR)
     */
    private function allowUnsafeEval(): bool
    {
        return $this->isDevelopment();
    }

    /**
     * Check if we're in a development environment
     */
    private function isDevelopment(): bool
    {
        $env = app()->environment();
        $isLocalEnv = in_array($env, ['local', 'development', 'dev'], true);
        $isDebugMode = config('app.debug', false);
        
        // Allow in local/development environments OR when debug mode is enabled
        return $isLocalEnv || $isDebugMode;
    }

    /**
     * Get Vite dev server hosts for CSP (supports both IPv4 and IPv6)
     */
    private function getViteHosts(): array
    {
        if ($this->isDevelopment()) {
            // Vite default dev server runs on localhost:5173
            // You can override this via .env if needed
            $viteHost = env('VITE_HOST', 'localhost');
            $vitePort = env('VITE_PORT', '5173');
            
            // Note: CSP does not support IPv6 addresses with brackets
            // Use localhost and 127.0.0.1 which cover most development scenarios
            $hosts = [
                "http://{$viteHost}:{$vitePort}",
                "http://127.0.0.1:{$vitePort}",
                "http://localhost:{$vitePort}",
            ];
            
            // Remove duplicates and filter out empty values
            return array_values(array_unique(array_filter($hosts)));
        }

        return [];
    }
}

