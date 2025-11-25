<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported locales
     */
    private const SUPPORTED_LOCALES = ['ro', 'en', 'ru'];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from various sources (priority order)
        $locale = $this->getLocale($request);

        // Set the application locale
        App::setLocale($locale);

        // Store in session for persistence
        Session::put('locale', $locale);

        return $next($request);
    }

    /**
     * Get the locale from request
     */
    private function getLocale(Request $request): string
    {
        // 1. Check if locale is in the request (from route or query parameter)
        if ($request->has('locale')) {
            $locale = $request->get('locale');
            if ($this->isValidLocale($locale)) {
                return $locale;
            }
        }

        // 2. Check session
        if (Session::has('locale')) {
            $locale = Session::get('locale');
            if ($this->isValidLocale($locale)) {
                return $locale;
            }
        }

        // 3. Check cookie
        if ($request->hasCookie('locale')) {
            $locale = $request->cookie('locale');
            if ($this->isValidLocale($locale)) {
                return $locale;
            }
        }

        // 4. Check Accept-Language header
        $preferredLocale = $request->getPreferredLanguage(self::SUPPORTED_LOCALES);
        if ($preferredLocale) {
            return $preferredLocale;
        }

        // 5. Fallback to config default
        return config('app.locale', 'en');
    }

    /**
     * Check if locale is valid
     */
    private function isValidLocale(?string $locale): bool
    {
        return $locale !== null && in_array($locale, self::SUPPORTED_LOCALES, true);
    }
}

