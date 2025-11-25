<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PromotionController extends Controller
{
    /**
     * Display a listing of all promotions.
     */
    public function index(): Response
    {
        return Inertia::render('Promotions/Index');
    }
}
