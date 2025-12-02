<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsRegularUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            abort(403, 'Acces interzis. Această pagină necesită autentificare.');
        }

        // Permite atât utilizatorilor obișnuiți, cât și adminilor
        $user = $request->user();
        if (!$user->hasRole('user') && !$user->hasRole('admin')) {
            abort(403, 'Acces interzis. Această pagină este disponibilă doar pentru utilizatori obișnuiți sau administratori.');
        }

        return $next($request);
    }
}
