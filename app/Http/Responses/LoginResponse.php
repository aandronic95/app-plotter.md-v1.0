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
        $redirect = redirect()->route('home');
        
        // Check if login modal is enabled in site settings
        $settings = \App\Models\SiteSetting::current();
        
        if ($settings->show_login_modal) {
            // Add flash message for login success with benefits
            $redirect->with('login_success', true);
        }
        
        return $request->wantsJson()
            ? new JsonResponse(['redirect' => route('home'), 'login_success' => $settings->show_login_modal], 200)
            : $redirect;
    }
}

