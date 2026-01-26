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

        foreach ($cart as $cartKey => $item) {
            // Extrage product_id din key sau din item
            $productId = $item['product_id'] ?? null;
            
            if (!$productId) {
                if (is_numeric($cartKey)) {
                    $productId = (int) $cartKey;
                } elseif (is_string($cartKey) && str_contains($cartKey, '_')) {
                    $parts = explode('_', $cartKey);
                    $productId = isset($parts[0]) && is_numeric($parts[0]) ? (int) $parts[0] : null;
                }
            }
            
            if (!$productId) {
                continue;
            }
            
            $product = Product::find($productId);
            if (!$product || !$product->is_active || !$product->in_stock) {
                continue;
            }

            // Pentru configurații, calculează subtotalul corect
            $subtotal = (float) $item['price'] * $item['quantity'];
            
            // Dacă există configurație, folosește prețul total din configurație
            if (isset($item['print_size']) && isset($item['print_sides']) && isset($item['configuration_quantity'])) {
                $configuration = $product->activeConfigurations()
                    ->where('print_size', $item['print_size'])
                    ->where('print_sides', $item['print_sides'])
                    ->where('quantity', $item['configuration_quantity'])
                    ->first();
                
                if ($configuration) {
                    // Pentru configurații, subtotalul este prețul total din configurație
                    $subtotal = (float) $configuration->price;
                }
            }

            $total += $subtotal;

            // Obține configurațiile disponibile pentru acest produs
            $configurations = [];
            if ($product->activeConfigurations()->exists()) {
                $configurations = $product->activeConfigurations()
                    ->orderBy('sort_order')
                    ->get()
                    ->map(function ($config) {
                        return [
                            'id' => $config->id,
                            'print_size' => $config->print_size,
                            'print_sides' => $config->print_sides,
                            'quantity' => $config->quantity,
                            'price' => (float) $config->price,
                            'price_per_unit' => (float) $config->price_per_unit,
                            'production_days' => $config->production_days,
                            'formatted_price' => $config->formatted_price,
                            'formatted_price_per_unit' => $config->formatted_price_per_unit,
                        ];
                    })
                    ->toArray();
            }

            $items[] = [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (float) $item['price'],
                'image' => $this->getImageUrl($product->image),
                'quantity' => $item['quantity'],
                'subtotal' => $subtotal,
                'stock_quantity' => $product->stock_quantity,
                'print_size' => $item['print_size'] ?? null,
                'print_sides' => $item['print_sides'] ?? null,
                'configuration_quantity' => $item['configuration_quantity'] ?? null,
                'configurations' => $configurations,
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
            'print_size' => 'nullable|string|in:A3,A4',
            'print_sides' => 'nullable|string|in:4+0,4+4',
            'configuration_quantity' => 'nullable|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if (!$product->is_active || !$product->in_stock) {
            return response()->json([
                'message' => 'Produsul nu este disponibil.',
            ], 400);
        }

        $cart = Session::get('cart', []);
        $quantity = (int) $request->quantity;

        // Creează un key unic pentru produs + configurație
        $cartKey = (string) $product->id;
        if ($request->print_size && $request->print_sides && $request->configuration_quantity) {
            $cartKey = "{$product->id}_{$request->print_size}_{$request->print_sides}_{$request->configuration_quantity}";
        }

        // Verifică stocul disponibil
        if (isset($cart[$cartKey])) {
            $currentQuantity = $cart[$cartKey]['quantity'];
            if ($currentQuantity + $quantity > $product->stock_quantity) {
                return response()->json([
                    'message' => 'Cantitatea disponibilă în stoc este insuficientă.',
                ], 400);
            }
            $cart[$cartKey]['quantity'] += $quantity;
        } else {
            if ($quantity > $product->stock_quantity) {
                return response()->json([
                    'message' => 'Cantitatea disponibilă în stoc este insuficientă.',
                ], 400);
            }
            
            // Determină prețul - folosește prețul din configurație dacă există
            $price = (float) $product->price;
            if ($request->print_size && $request->print_sides && $request->configuration_quantity) {
                $configuration = $product->activeConfigurations()
                    ->where('print_size', $request->print_size)
                    ->where('print_sides', $request->print_sides)
                    ->where('quantity', $request->configuration_quantity)
                    ->first();
                
                if ($configuration) {
                    $price = (float) $configuration->price_per_unit;
                }
            }
            
            $cart[$cartKey] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $price,
                'print_size' => $request->print_size,
                'print_sides' => $request->print_sides,
                'configuration_quantity' => $request->configuration_quantity,
            ];
        }

        Session::put('cart', $cart);

        return $this->index();
    }

    /**
     * Update product quantity or configuration in cart.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'print_size' => 'nullable|string|in:A3,A4',
            'print_sides' => 'nullable|string|in:4+0,4+4',
            'configuration_quantity' => 'nullable|integer|min:1',
            'old_print_size' => 'nullable|string',
            'old_print_sides' => 'nullable|string',
            'old_configuration_quantity' => 'nullable|integer',
        ]);

        $product = Product::findOrFail($request->product_id);
        $cart = Session::get('cart', []);
        $quantity = (int) $request->quantity;

        // Găsește item-ul vechi în cart
        $oldCartKey = (string) $product->id;
        if ($request->old_print_size && $request->old_print_sides && $request->old_configuration_quantity) {
            $oldCartKey = "{$product->id}_{$request->old_print_size}_{$request->old_print_sides}_{$request->old_configuration_quantity}";
        }

        // Caută item-ul în cart
        $foundKey = null;
        foreach ($cart as $key => $item) {
            $itemProductId = $item['product_id'] ?? null;
            
            if (!$itemProductId) {
                if (is_numeric($key)) {
                    $itemProductId = (int) $key;
                } elseif (is_string($key) && str_contains($key, '_')) {
                    $parts = explode('_', $key);
                    $itemProductId = isset($parts[0]) && is_numeric($parts[0]) ? (int) $parts[0] : null;
                }
            }
            
            if ($itemProductId == $product->id) {
                // Verifică dacă configurația veche se potrivește
                $oldSize = $item['print_size'] ?? null;
                $oldSides = $item['print_sides'] ?? null;
                $oldConfigQty = $item['configuration_quantity'] ?? null;
                
                if ((!$request->old_print_size && !$oldSize) || ($request->old_print_size == $oldSize)) {
                    if ((!$request->old_print_sides && !$oldSides) || ($request->old_print_sides == $oldSides)) {
                        if ((!$request->old_configuration_quantity && !$oldConfigQty) || ($request->old_configuration_quantity == $oldConfigQty)) {
                            $foundKey = $key;
                            break;
                        }
                    }
                }
            }
        }

        if (!$foundKey) {
            return response()->json([
                'message' => 'Produsul nu se află în coș.',
            ], 404);
        }

        if ($quantity > $product->stock_quantity) {
            return response()->json([
                'message' => 'Cantitatea disponibilă în stoc este insuficientă.',
            ], 400);
        }

        // Dacă configurația s-a schimbat, șterge item-ul vechi și adaugă-l cu noua configurație
        $oldItem = $cart[$foundKey];
        $newPrintSize = $request->print_size ?? $oldItem['print_size'] ?? null;
        $newPrintSides = $request->print_sides ?? $oldItem['print_sides'] ?? null;
        $newConfigQty = $request->configuration_quantity ?? $oldItem['configuration_quantity'] ?? null;

        // Verifică dacă configurația s-a schimbat
        $configChanged = ($newPrintSize != ($oldItem['print_size'] ?? null)) ||
                        ($newPrintSides != ($oldItem['print_sides'] ?? null)) ||
                        ($newConfigQty != ($oldItem['configuration_quantity'] ?? null));

        if ($configChanged) {
            // Șterge item-ul vechi
            unset($cart[$foundKey]);

            // Calculează noul preț
            $price = (float) $product->price;
            if ($newPrintSize && $newPrintSides && $newConfigQty) {
                $configuration = $product->activeConfigurations()
                    ->where('print_size', $newPrintSize)
                    ->where('print_sides', $newPrintSides)
                    ->where('quantity', $newConfigQty)
                    ->first();
                
                if ($configuration) {
                    $price = (float) $configuration->price_per_unit;
                }
            }

            // Creează noul key
            $newCartKey = (string) $product->id;
            if ($newPrintSize && $newPrintSides && $newConfigQty) {
                $newCartKey = "{$product->id}_{$newPrintSize}_{$newPrintSides}_{$newConfigQty}";
            }

            // Verifică dacă există deja un item cu noua configurație
            if (isset($cart[$newCartKey])) {
                $cart[$newCartKey]['quantity'] += $quantity;
            } else {
                $cart[$newCartKey] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'print_size' => $newPrintSize,
                    'print_sides' => $newPrintSides,
                    'configuration_quantity' => $newConfigQty,
                ];
            }
        } else {
            // Doar actualizează cantitatea
            $cart[$foundKey]['quantity'] = $quantity;
        }

        Session::put('cart', $cart);

        return $this->index();
    }

    /**
     * Remove product from cart.
     */
    public function remove(int $productId, Request $request = null): JsonResponse
    {
        $cart = Session::get('cart', []);

        // Dacă există configurație în request, șterge item-ul specific
        if ($request && $request->has('print_size') && $request->has('print_sides') && $request->has('configuration_quantity')) {
            $cartKey = "{$productId}_{$request->print_size}_{$request->print_sides}_{$request->configuration_quantity}";
            if (isset($cart[$cartKey])) {
                unset($cart[$cartKey]);
                Session::put('cart', $cart);
                return $this->index();
            }
        }

        // Caută item-ul în cart - poate fi cu key simplu sau compus
        $foundKey = null;
        
        // Verifică dacă există direct cu key simplu
        if (isset($cart[$productId])) {
            $foundKey = $productId;
        } else {
            // Caută în toate keys-urile
            foreach ($cart as $key => $item) {
                // Extrage product_id din item sau din key
                $itemProductId = null;
                
                if (isset($item['product_id'])) {
                    $itemProductId = $item['product_id'];
                } elseif (is_numeric($key)) {
                    $itemProductId = (int) $key;
                } elseif (is_string($key) && str_contains($key, '_')) {
                    // Key compus: productId_size_sides_quantity
                    $parts = explode('_', $key);
                    $itemProductId = isset($parts[0]) && is_numeric($parts[0]) ? (int) $parts[0] : null;
                }
                
                // Dacă nu există configurație în request, șterge primul item găsit pentru acest produs
                if ($itemProductId == $productId) {
                    if (!$request || (!$request->has('print_size') && !$request->has('print_sides') && !$request->has('configuration_quantity'))) {
                        $foundKey = $key;
                        break;
                    }
                    // Dacă există configurație în request, verifică dacă se potrivește
                    if ($request && $request->has('print_size') && $request->has('print_sides') && $request->has('configuration_quantity')) {
                        if (($item['print_size'] ?? null) == $request->print_size &&
                            ($item['print_sides'] ?? null) == $request->print_sides &&
                            ($item['configuration_quantity'] ?? null) == $request->configuration_quantity) {
                            $foundKey = $key;
                            break;
                        }
                    }
                }
            }
        }

        if ($foundKey && isset($cart[$foundKey])) {
            unset($cart[$foundKey]);
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
