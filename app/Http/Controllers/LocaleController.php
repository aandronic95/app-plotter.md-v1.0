<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    /**
     * Supported locales
     */
    private const SUPPORTED_LOCALES = ['ro', 'en', 'ru'];

    /**
     * Switch application locale
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (!in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = config('app.locale', 'en');
        }

        // Set locale
        App::setLocale($locale);
        Session::put('locale', $locale);

        // Redirect back or to home
        $redirectTo = $request->get('redirect', '/');

        return redirect($redirectTo)->withCookie(
            cookie()->forever('locale', $locale)
        );
    }
}

