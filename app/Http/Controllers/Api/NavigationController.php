<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NavigationResource;
use App\Models\Navigation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NavigationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $group = $request->get('group', 'main');
        $category = $request->get('category');
        
        $query = Navigation::query()
            ->where('group', $group)
            ->where('is_active', true);

        if ($category) {
            $query->where('category', $category);
        }
        
        $navigations = $query->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return response()->json([
            'data' => NavigationResource::collection($navigations),
        ]);
    }

    /**
     * Get all distinct categories from navigations.
     */
    public function categories(Request $request): JsonResponse
    {
        $group = $request->get('group', 'header');
        
        try {
            // Fetch all navigations for the group in a single query
            $allNavigations = Navigation::query()
                ->where('group', $group)
                ->where('is_active', true)
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->orderBy('category')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get();
            
            // Group by category in memory (much faster than N+1 queries)
            $grouped = $allNavigations->groupBy('category');
            
            // Build categories with their items
            $categories = $grouped->map(function ($items, $categoryName) {
                return [
                    'name' => $categoryName,
                    'items' => NavigationResource::collection($items),
                ];
            })->values();

            return response()->json([
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching categories:', [
                'message' => $e->getMessage(),
                'group' => $group,
            ]);
            
            return response()->json([
                'data' => [],
                'error' => 'Error fetching categories',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'href' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'group' => 'required|string|in:main,footer,header',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'is_external' => 'nullable|boolean',
            'target' => 'nullable|string|in:_self,_blank,_parent,_top',
            'description' => 'nullable|string|max:500',
        ]);

        $navigation = Navigation::create($validated);

        return response()->json([
            'data' => new NavigationResource($navigation),
            'message' => 'Elementul de navigare a fost creat cu succes.',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Navigation $navigation): JsonResponse
    {
        return response()->json([
            'data' => new NavigationResource($navigation),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Navigation $navigation): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'href' => 'sometimes|required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'group' => 'sometimes|required|string|in:main,footer,header',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'is_external' => 'nullable|boolean',
            'target' => 'nullable|string|in:_self,_blank,_parent,_top',
            'description' => 'nullable|string|max:500',
        ]);

        $navigation->update($validated);

        return response()->json([
            'data' => new NavigationResource($navigation),
            'message' => 'Elementul de navigare a fost actualizat cu succes.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Navigation $navigation): JsonResponse
    {
        $navigation->delete();

        return response()->json([
            'message' => 'Elementul de navigare a fost șters cu succes.',
        ]);
    }
}
