<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AboutSectionResource;
use App\Models\AboutSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AboutSectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AboutSection::query();

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
        $sections = $query->paginate($perPage);

        // Get pagination links in Laravel's standard format
        $linkCollection = $sections->linkCollection();
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
            
            $relativeUrl = '/api/about-sections';
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
            'data' => AboutSectionResource::collection($sections->items()),
            'links' => $links,
            'meta' => [
                'current_page' => $sections->currentPage(),
                'last_page' => $sections->lastPage(),
                'per_page' => $sections->perPage(),
                'total' => $sections->total(),
                'from' => $sections->firstItem(),
                'to' => $sections->lastItem(),
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
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $section = AboutSection::create($validated);

        return response()->json([
            'data' => new AboutSectionResource($section),
            'message' => 'Secțiunea a fost creată cu succes.',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(AboutSection $aboutSection): JsonResponse
    {
        return response()->json([
            'data' => new AboutSectionResource($aboutSection),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AboutSection $aboutSection): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $aboutSection->update($validated);

        return response()->json([
            'data' => new AboutSectionResource($aboutSection),
            'message' => 'Secțiunea a fost actualizată cu succes.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AboutSection $aboutSection): JsonResponse
    {
        $aboutSection->delete();

        return response()->json([
            'message' => 'Secțiunea a fost ștearsă cu succes.',
        ]);
    }
}

