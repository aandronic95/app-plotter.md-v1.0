<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PortfolioResource;
use App\Models\Portfolio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Portfolio::query();

        // Filter by active status
        if ($request->boolean('active_only')) {
            $query->active();
        }

        // Order by created_at desc by default
        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Pagination
        $perPage = min((int) $request->get('per_page', 15), 100);
        $portfolios = $query->paginate($perPage);

        // Get pagination links in Laravel's standard format
        $linkCollection = $portfolios->linkCollection();
        $links = $linkCollection->map(function ($link) {
            $url = $link['url'] ?? null;
            
            if ($url === null) {
                return [
                    'url' => null,
                    'label' => $link['label'] ?? '',
                    'active' => $link['active'] ?? false,
                ];
            }
            
            $parsedUrl = parse_url($url);
            $queryString = $parsedUrl['query'] ?? '';
            
            $relativeUrl = '/api/portfolios';
            if ($queryString) {
                $relativeUrl .= '?' . $queryString;
            }
            
            return [
                'url' => $relativeUrl,
                'label' => $link['label'] ?? '',
                'active' => $link['active'] ?? false,
            ];
        })->toArray();

        return response()->json([
            'data' => PortfolioResource::collection($portfolios->items()),
            'links' => $links,
            'meta' => [
                'current_page' => $portfolios->currentPage(),
                'last_page' => $portfolios->lastPage(),
                'per_page' => $portfolios->perPage(),
                'total' => $portfolios->total(),
                'from' => $portfolios->firstItem(),
                'to' => $portfolios->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:portfolios,slug',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'images' => 'nullable|array',
            'images.*' => 'string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $portfolio = Portfolio::create($validated);

        return response()->json([
            'data' => new PortfolioResource($portfolio),
            'message' => 'Lucrarea a fost creată cu succes.',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Portfolio $portfolio): JsonResponse
    {
        return response()->json([
            'data' => new PortfolioResource($portfolio),
        ]);
    }

    /**
     * Display the specified resource by slug.
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $portfolio = Portfolio::where('slug', $slug)->first();
        
        if (!$portfolio) {
            return response()->json([
                'data' => null,
                'message' => 'Portfolio nu a fost găsit.',
            ], 404);
        }
        
        return response()->json([
            'data' => new PortfolioResource($portfolio),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Portfolio $portfolio): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|nullable|string|max:255|unique:portfolios,slug,' . $portfolio->id,
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'images' => 'nullable|array',
            'images.*' => 'string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $portfolio->update($validated);

        return response()->json([
            'data' => new PortfolioResource($portfolio),
            'message' => 'Lucrarea a fost actualizată cu succes.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Portfolio $portfolio): JsonResponse
    {
        $portfolio->delete();

        return response()->json([
            'message' => 'Lucrarea a fost ștearsă cu succes.',
        ]);
    }
}

