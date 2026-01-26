<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\PortfolioResource;
use App\Models\Portfolio;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortfolioController extends Controller
{
    /**
     * Display the specified portfolio.
     */
    public function show(string $slug): Response
    {
        $portfolio = Portfolio::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$portfolio) {
            return Inertia::render('Portfolios/Show', [
                'portfolio' => null,
            ]);
        }

        $portfolioData = (new PortfolioResource($portfolio))->toArray(request());

        return Inertia::render('Portfolios/Show', [
            'portfolio' => $portfolioData,
        ]);
    }
}

