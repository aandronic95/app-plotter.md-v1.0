<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HeroBannerResource;
use App\Models\HeroBanner;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HeroBannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        // Check if hero banner data exists in site_settings
        $siteSettings = SiteSetting::current();
        
        // Check if site_settings has hero banner data and it's active
        // Consider it has data if title exists OR if is_active is explicitly set
        $hasSiteSettingsBanner = !empty($siteSettings->hero_banner_title) 
            || ($siteSettings->hero_banner_is_active !== null);
        
        // Check if banner should be active (default to true if not set)
        $isActive = $siteSettings->hero_banner_is_active ?? true;
        
        // If site_settings has hero banner data and active_only is requested, return it
        // Only return if active_only is true AND banner is active, OR if active_only is false
        $shouldReturnSiteSettings = $hasSiteSettingsBanner 
            && (!$request->boolean('active_only') || $isActive);
        
        if ($shouldReturnSiteSettings) {
            $perPage = min((int) $request->get('per_page', 15), 100);
            
            // Get image path (relative, not full URL, so HeroBannerResource can handle it)
            $imagePath = $siteSettings->hero_banner_image;
            
            // Transform site_settings data to HeroBanner format
            $bannerData = [
                'id' => 1,
                'headline' => $siteSettings->hero_banner_headline,
                'title' => $siteSettings->hero_banner_title ?? 'PRINTĂM',
                'description' => $siteSettings->hero_banner_description,
                'features' => $siteSettings->hero_banner_features_array ?? [],
                'button1_text' => $siteSettings->hero_banner_button1_text,
                'button1_link' => $siteSettings->hero_banner_button1_link,
                'button2_text' => $siteSettings->hero_banner_button2_text,
                'button2_link' => $siteSettings->hero_banner_button2_link,
                'image' => $imagePath, // Store relative path, HeroBannerResource will convert to URL
                'is_active' => $isActive,
                'sort_order' => $siteSettings->hero_banner_sort_order ?? 0,
                'rotating_words' => $siteSettings->hero_banner_rotating_words_array ?? ['HAINE', 'CĂRȚI DE VIZITE', 'BANERE', 'CUTII', 'POSTERE'],
                'created_at' => $siteSettings->created_at,
                'updated_at' => $siteSettings->updated_at,
            ];
            
            // Create a HeroBanner model instance for the resource using make()
            $banner = HeroBanner::make($bannerData);
            $banner->id = 1;
            $banner->exists = true;
            
            // Ensure timestamps are set correctly
            if ($siteSettings->created_at) {
                $banner->created_at = $siteSettings->created_at;
            }
            if ($siteSettings->updated_at) {
                $banner->updated_at = $siteSettings->updated_at;
            }
            
            return response()->json([
                'data' => [new HeroBannerResource($banner)],
                'links' => [
                    [
                        'url' => null,
                        'label' => '&laquo; Previous',
                        'active' => false,
                    ],
                    [
                        'url' => '/api/hero-banners?page=1',
                        'label' => '1',
                        'active' => true,
                    ],
                    [
                        'url' => null,
                        'label' => 'Next &raquo;',
                        'active' => false,
                    ],
                ],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $perPage,
                    'total' => 1,
                    'from' => 1,
                    'to' => 1,
                ],
            ]);
        }
        
        // Otherwise, use the hero_banners table
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
