<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HeaderContact;
use Illuminate\Http\JsonResponse;

class HeaderContactController extends Controller
{
    /**
     * Get all active header contacts.
     */
    public function index(): JsonResponse
    {
        $contacts = HeaderContact::getActive();

        return response()->json([
            'data' => $contacts,
        ]);
    }
}
