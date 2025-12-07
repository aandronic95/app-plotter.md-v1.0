<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    /**
     * Store a newly created newsletter subscriber.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:newsletter_subscribers,email',
            'phone' => 'nullable|string|max:20',
            'privacy_accepted' => 'required|accepted',
        ], [
            'name.required' => 'Numele este obligatoriu.',
            'email.required' => 'Email-ul este obligatoriu.',
            'email.email' => 'Email-ul trebuie să fie valid.',
            'email.unique' => 'Acest email este deja înregistrat.',
            'privacy_accepted.required' => 'Trebuie să acceptați politica de confidențialitate.',
            'privacy_accepted.accepted' => 'Trebuie să acceptați politica de confidențialitate.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datele introduse nu sunt valide.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $subscriber = NewsletterSubscriber::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'privacy_accepted' => true,
            ]);

            return response()->json([
                'message' => 'V-ați abonat cu succes la newsletter!',
                'data' => [
                    'id' => $subscriber->id,
                    'name' => $subscriber->name,
                    'email' => $subscriber->email,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'A apărut o eroare. Vă rugăm să încercați din nou.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
