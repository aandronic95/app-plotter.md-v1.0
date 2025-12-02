<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return Response
     */
    public function toResponse($request): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('admin')) {
            return $request->wantsJson()
                ? new JsonResponse(['redirect' => '/admin'], 200)
                : redirect('/admin');
        }

        return $request->wantsJson()
            ? new JsonResponse(['redirect' => '/profile'], 200)
            : redirect('/profile');
    }
}

