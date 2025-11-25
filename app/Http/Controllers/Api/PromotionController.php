<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromotionResource;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Promotion::query();

        // Filter by active status
        if ($request->boolean('active_only')) {
            $query->active();
        }

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Order by
        $orderBy = $request->get('order_by', 'sort_order');
        $orderDir = $request->get('order_dir', 'asc');
        $query->orderBy($orderBy, $orderDir);

        // Pagination
        $perPage = min((int) $request->get('per_page', 15), 100);
        $promotions = $query->with(['page', 'product'])->paginate($perPage);

        // Get pagination links in Laravel's standard format
        $linkCollection = $promotions->linkCollection();
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
            
            $relativeUrl = '/api/promotions';
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
            'data' => PromotionResource::collection($promotions->items()),
            'links' => $links,
            'meta' => [
                'current_page' => $promotions->currentPage(),
                'last_page' => $promotions->lastPage(),
                'per_page' => $promotions->perPage(),
                'total' => $promotions->total(),
                'from' => $promotions->firstItem(),
                'to' => $promotions->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner' => 'required|string|max:500',
            'external_link' => 'nullable|url|max:500',
            'page_id' => 'nullable|exists:pages,id',
            'product_id' => 'nullable|exists:products,id',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'end_date' => 'nullable|date|after:today',
        ]);

        // Ensure at least one link type is provided if external_link is not set
        if (empty($validated['external_link']) && empty($validated['page_id']) && empty($validated['product_id'])) {
            return response()->json([
                'message' => 'Trebuie să specificați fie un link extern, fie o pagină, fie un produs.',
            ], 422);
        }

        $promotion = Promotion::create($validated);

        return response()->json([
            'data' => new PromotionResource($promotion->load(['page', 'product'])),
            'message' => 'Promoția a fost creată cu succes.',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Promotion $promotion): JsonResponse
    {
        return response()->json([
            'data' => new PromotionResource($promotion->load(['page', 'product'])),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Promotion $promotion): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'banner' => 'sometimes|required|string|max:500',
            'external_link' => 'nullable|url|max:500',
            'page_id' => 'nullable|exists:pages,id',
            'product_id' => 'nullable|exists:products,id',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'end_date' => 'nullable|date|after:today',
        ]);

        // Ensure at least one link type is provided if external_link is not set
        $externalLink = $validated['external_link'] ?? $promotion->external_link;
        $pageId = $validated['page_id'] ?? $promotion->page_id;
        $productId = $validated['product_id'] ?? $promotion->product_id;

        if (empty($externalLink) && empty($pageId) && empty($productId)) {
            return response()->json([
                'message' => 'Trebuie să specificați fie un link extern, fie o pagină, fie un produs.',
            ], 422);
        }

        $promotion->update($validated);

        return response()->json([
            'data' => new PromotionResource($promotion->load(['page', 'product'])),
            'message' => 'Promoția a fost actualizată cu succes.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Promotion $promotion): JsonResponse
    {
        $promotion->delete();

        return response()->json([
            'message' => 'Promoția a fost ștearsă cu succes.',
        ]);
    }
}
