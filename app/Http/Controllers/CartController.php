<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class CartController extends Controller
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
     * Get cart contents.
     */
    public function index(): JsonResponse
    {
        $cart = Session::get('cart', []);
        $items = [];
        $total = 0;

        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if (!$product || !$product->is_active || !$product->in_stock) {
                continue;
            }

            $subtotal = (float) $item['price'] * $item['quantity'];
            $total += $subtotal;

            $items[] = [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (float) $item['price'],
                'image' => $this->getImageUrl($product->image),
                'quantity' => $item['quantity'],
                'subtotal' => $subtotal,
                'stock_quantity' => $product->stock_quantity,
            ];
        }

        return response()->json([
            'items' => $items,
            'total' => $total,
            'count' => array_sum(array_column($cart, 'quantity')),
        ]);
    }

    /**
     * Add product to cart.
     */
    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if (!$product->is_active || !$product->in_stock) {
            return response()->json([
                'message' => 'Produsul nu este disponibil.',
            ], 400);
        }

        $cart = Session::get('cart', []);
        $quantity = (int) $request->quantity;

        // Verifică stocul disponibil
        if (isset($cart[$product->id])) {
            $currentQuantity = $cart[$product->id]['quantity'];
            if ($currentQuantity + $quantity > $product->stock_quantity) {
                return response()->json([
                    'message' => 'Cantitatea disponibilă în stoc este insuficientă.',
                ], 400);
            }
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            if ($quantity > $product->stock_quantity) {
                return response()->json([
                    'message' => 'Cantitatea disponibilă în stoc este insuficientă.',
                ], 400);
            }
            $cart[$product->id] = [
                'quantity' => $quantity,
                'price' => (float) $product->price,
            ];
        }

        Session::put('cart', $cart);

        return $this->index();
    }

    /**
     * Update product quantity in cart.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $cart = Session::get('cart', []);
        $quantity = (int) $request->quantity;

        if (!isset($cart[$product->id])) {
            return response()->json([
                'message' => 'Produsul nu se află în coș.',
            ], 404);
        }

        if ($quantity > $product->stock_quantity) {
            return response()->json([
                'message' => 'Cantitatea disponibilă în stoc este insuficientă.',
            ], 400);
        }

        $cart[$product->id]['quantity'] = $quantity;
        Session::put('cart', $cart);

        return $this->index();
    }

    /**
     * Remove product from cart.
     */
    public function remove(int $productId): JsonResponse
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::put('cart', $cart);
        }

        return $this->index();
    }

    /**
     * Clear cart.
     */
    public function clear(): JsonResponse
    {
        Session::forget('cart');

        return response()->json([
            'message' => 'Coșul a fost golit.',
            'items' => [],
            'total' => 0,
            'count' => 0,
        ]);
    }
}
