<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class WishlistController extends Controller
{
    /**
     * Get image URL from path.
     */
    private function getImageUrl(?string $image): string
    {
        if (empty($image)) {
            return '/images/placeholder.jpg';
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        return Storage::disk('public')->url($image);
    }

    /**
     * Get all wishlist items for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $wishlistItems = Wishlist::where('user_id', $user->id)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (Wishlist $item) {
                $product = $item->product;
                if (!$product) {
                    return null;
                }

                return [
                    'id' => $item->id,
                    'product_id' => $product->id,
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'price' => (float) $product->price,
                        'original_price' => $product->original_price ? (float) $product->original_price : null,
                        'image' => $this->getImageUrl($product->image),
                        'discount' => $product->discount,
                        'in_stock' => $product->in_stock,
                        'stock_quantity' => $product->stock_quantity,
                    ],
                    'created_at' => $item->created_at->format('d.m.Y H:i'),
                ];
            })
            ->filter();

        return response()->json([
            'wishlist' => $wishlistItems->values(),
        ]);
    }

    /**
     * Add a product to wishlist.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = $request->user();

        // Check if product is already in wishlist
        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existing) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Produsul este deja în wishlist.',
                    'in_wishlist' => true,
                ], 422);
            }

            return redirect()->back()->with('error', 'Produsul este deja în wishlist.');
        }

        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $validated['product_id'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Produsul a fost adăugat în wishlist.',
                'wishlist_item' => [
                    'id' => $wishlist->id,
                    'product_id' => $wishlist->product_id,
                ],
            ], 201);
        }

        return redirect()->back()->with('success', 'Produsul a fost adăugat în wishlist.');
    }

    /**
     * Remove a product from wishlist.
     */
    public function destroy(Request $request, int $productId): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $wishlist = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->firstOrFail();

        $wishlist->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Produsul a fost eliminat din wishlist.',
            ]);
        }

        return redirect()->back()->with('success', 'Produsul a fost eliminat din wishlist.');
    }

    /**
     * Check if a product is in wishlist.
     */
    public function check(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();

        $inWishlist = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();

        return response()->json([
            'in_wishlist' => $inWishlist,
        ]);
    }

    /**
     * Batch check multiple products for wishlist status.
     */
    public function checkBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'required|integer|exists:products,id',
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'data' => [],
            ]);
        }

        $wishlistProductIds = Wishlist::where('user_id', $user->id)
            ->whereIn('product_id', $validated['product_ids'])
            ->pluck('product_id')
            ->toArray();

        $result = [];
        foreach ($validated['product_ids'] as $productId) {
            $result[$productId] = in_array($productId, $wishlistProductIds, true);
        }

        return response()->json([
            'data' => $result,
        ]);
    }
}
