<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    /**
     * Display a listing of pages.
     */
    public function index(Request $request): Response
    {
        $pages = Page::published()
            ->ordered()
            ->paginate(15);

        return Inertia::render('Pages/Index', [
            'pages' => $pages,
        ]);
    }

    /**
     * Display the specified page.
     */
    public function show(string $slug): Response
    {
        $page = Page::where('slug', $slug)
            ->published()
            ->firstOrFail();

        return Inertia::render('Pages/Show', [
            'page' => $page,
        ]);
    }
}

