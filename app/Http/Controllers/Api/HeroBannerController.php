<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HeroBannerResource;
use App\Models\HeroBanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HeroBannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = HeroBanner::query();

        // Filter by active status
        if ($request->boolean('active_only')) {
            $query->active();
        }

        // Order by
        $orderBy = $request->get('order_by', 'sort_order');
        $orderDir = $request->get('order_dir', 'asc');
        $query->orderBy($orderBy, $orderDir);

        // Pagination
        $perPage = min((int) $request->get('per_page', 15), 100);
        $banners = $query->paginate($perPage);

        // Get pagination links in Laravel's standard format
        $linkCollection = $banners->linkCollection();
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
            
            $relativeUrl = '/api/hero-banners';
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
            'data' => HeroBannerResource::collection($banners->items()),
            'links' => $links,
            'meta' => [
                'current_page' => $banners->currentPage(),
                'last_page' => $banners->lastPage(),
                'per_page' => $banners->perPage(),
                'total' => $banners->total(),
                'from' => $banners->firstItem(),
                'to' => $banners->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'headline' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'button1_text' => 'nullable|string|max:255',
            'button1_link' => 'nullable|string|max:500',
            'button2_text' => 'nullable|string|max:255',
            'button2_link' => 'nullable|string|max:500',
            'image' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $banner = HeroBanner::create($validated);

        return response()->json([
            'data' => new HeroBannerResource($banner),
            'message' => 'Bannerul a fost creat cu succes.',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(HeroBanner $heroBanner): JsonResponse
    {
        return response()->json([
            'data' => new HeroBannerResource($heroBanner),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HeroBanner $heroBanner): JsonResponse
    {
        $validated = $request->validate([
            'headline' => 'sometimes|nullable|string|max:255',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'button1_text' => 'nullable|string|max:255',
            'button1_link' => 'nullable|string|max:500',
            'button2_text' => 'nullable|string|max:255',
            'button2_link' => 'nullable|string|max:500',
            'image' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $heroBanner->update($validated);

        return response()->json([
            'data' => new HeroBannerResource($heroBanner),
            'message' => 'Bannerul a fost actualizat cu succes.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HeroBanner $heroBanner): JsonResponse
    {
        $heroBanner->delete();

        return response()->json([
            'message' => 'Bannerul a fost șters cu succes.',
        ]);
    }
}
