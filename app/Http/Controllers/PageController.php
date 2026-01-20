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
        // First check if page exists (without published scope for better error handling)
        $page = Page::where('slug', $slug)->first();
        
        if (!$page) {
            abort(404, 'Pagina nu a fost găsită.');
        }
        
        // Check if page is published
        if (!$page->is_published || !$page->is_active) {
            abort(404, 'Pagina nu este publicată sau nu este activă.');
        }
        
        // Check published_at date if set
        if ($page->published_at && $page->published_at->isFuture()) {
            abort(404, 'Pagina nu este încă publicată.');
        }

        return Inertia::render('Pages/Show', [
            'page' => $page,
        ]);
    }
}

