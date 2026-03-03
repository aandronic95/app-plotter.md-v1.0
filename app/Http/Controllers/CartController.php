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
    public const MINIMUM_ORDER_AMOUNT = 300;

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
     * Find cart key for an item by product_id and optional config params.
     */
    private function findCartKey(array $cart, int $productId, ?string $printSize = null, ?string $printSides = null, ?string $format = null, ?string $suport = null, ?string $culoare = null, ?string $colturi = null, ?int $configurationQuantity = null): ?string
    {
        foreach ($cart as $key => $item) {
            $itemProductId = $item['product_id'] ?? null;
            if (!$itemProductId && is_numeric($key)) {
                $itemProductId = (int) $key;
            }
            if (!$itemProductId && is_string($key) && str_contains($key, '_')) {
                $parts = explode('_', $key);
                $itemProductId = isset($parts[0]) && is_numeric($parts[0]) ? (int) $parts[0] : null;
            }
            if ((int) $itemProductId !== $productId) {
                continue;
            }
            $itemSize = isset($item['print_size']) && $item['print_size'] !== '' ? $item['print_size'] : null;
            $itemSides = isset($item['print_sides']) && $item['print_sides'] !== '' ? $item['print_sides'] : null;
            $itemFormat = isset($item['format']) && $item['format'] !== '' ? $item['format'] : null;
            $itemSuport = isset($item['suport']) && $item['suport'] !== '' ? $item['suport'] : null;
            $itemCuloare = isset($item['culoare']) && $item['culoare'] !== '' ? $item['culoare'] : null;
            $itemColturi = isset($item['colturi']) && $item['colturi'] !== '' ? $item['colturi'] : null;
            $itemConfigQty = isset($item['configuration_quantity']) ? (int) $item['configuration_quantity'] : null;
            if ($printSize !== null || $printSides !== null || $configurationQuantity !== null) {
                if (($printSize !== null && $itemSize != $printSize) ||
                    ($printSides !== null && $itemSides != $printSides) ||
                    ($format !== null && $itemFormat != $format) ||
                    ($suport !== null && $itemSuport != $suport) ||
                    ($culoare !== null && $itemCuloare != $culoare) ||
                    ($colturi !== null && $itemColturi != $colturi) ||
                    ($configurationQuantity !== null && $itemConfigQty != $configurationQuantity)) {
                    continue;
                }
            } elseif ($itemSize !== null || $itemSides !== null || $itemConfigQty !== null) {
                continue;
            }
            return $key;
        }
        return null;
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
            
            $product = Product::with('category')->find($productId);
            if (!$product || !$product->is_active || !$product->in_stock) {
                continue;
            }

            // Pentru configurații, calculează subtotalul: pret_bucata × coeficient × tiraj × cantitate
            $subtotal = (float) $item['price'] * $item['quantity'];
            
            if (isset($item['print_size']) && isset($item['print_sides']) && isset($item['configuration_quantity'])) {
                $query = $product->activeConfigurations()
                    ->where('print_size', $item['print_size'])
                    ->where('print_sides', $item['print_sides'])
                    ->where('quantity', $item['configuration_quantity']);
                
                if (isset($item['format']) && $item['format']) {
                    $query->where('format', $item['format']);
                } else {
                    $query->where(function ($q) {
                        $q->whereNull('format')->orWhere('format', '');
                    });
                }
                
                if (isset($item['suport']) && $item['suport']) {
                    $query->where('suport', $item['suport']);
                } else {
                    $query->where(function ($q) {
                        $q->whereNull('suport')->orWhere('suport', '');
                    });
                }
                
                if (isset($item['culoare']) && $item['culoare']) {
                    $query->where('culoare', $item['culoare']);
                } else {
                    $query->where(function ($q) {
                        $q->whereNull('culoare')->orWhere('culoare', '');
                    });
                }
                
                if (isset($item['colturi']) && $item['colturi']) {
                    $query->where('colturi', $item['colturi']);
                } else {
                    $query->where(function ($q) {
                        $q->whereNull('colturi')->orWhere('colturi', '');
                    });
                }
                
                $configuration = $query->first();
                
                if ($configuration && $product->category) {
                    $coef = $product->category->getFormatPriceCoefficient($item['format'] ?? '');
                    $subtotal = (float) $configuration->price_per_unit * $coef * (int) $item['configuration_quantity'] * $item['quantity'];
                }
            }

            if (!empty($item['elaborate_mockup']) && isset($item['elaborate_mockup_price']) && (float) $item['elaborate_mockup_price'] > 0) {
                $subtotal += (float) $item['elaborate_mockup_price'];
            }

            $total += $subtotal;

            // Obține configurațiile disponibile pentru acest produs (cu preț efectiv = pret_bucata × coeficient)
            $configurations = [];
            if ($product->activeConfigurations()->exists()) {
                $category = $product->category;
                $configurations = $product->activeConfigurations()
                    ->orderBy('sort_order')
                    ->get()
                    ->map(function ($config) use ($category) {
                        $coef = $category ? $category->getFormatPriceCoefficient($config->format ?? '') : 1.0;
                        $effectivePricePerUnit = (float) $config->price_per_unit * $coef;
                        $effectivePrice = $effectivePricePerUnit * (int) $config->quantity;
                        return [
                            'id' => $config->id,
                            'print_size' => $config->print_size,
                            'print_sides' => $config->print_sides,
                            'format' => $config->format,
                            'suport' => $config->suport,
                            'culoare' => $config->culoare,
                            'colturi' => $config->colturi,
                            'quantity' => $config->quantity,
                            'price' => (float) $config->price,
                            'price_per_unit' => (float) $config->price_per_unit,
                            'price_coefficient' => $coef,
                            'effective_price_per_unit' => $effectivePricePerUnit,
                            'effective_price' => $effectivePrice,
                            'formatted_effective_price_per_unit' => number_format($effectivePricePerUnit, 2, ',', '.') . ' LEI',
                            'formatted_effective_price' => number_format($effectivePrice, 2, ',', '.') . ' LEI',
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
                'format' => $item['format'] ?? null,
                'suport' => $item['suport'] ?? null,
                'culoare' => $item['culoare'] ?? null,
                'colturi' => $item['colturi'] ?? null,
                'configuration_quantity' => $item['configuration_quantity'] ?? null,
                'configurations' => $configurations,
                'mockup_path' => $item['mockup_path'] ?? null,
                'mockup_filename' => $item['mockup_filename'] ?? null,
                'mockup_url' => !empty($item['mockup_path']) ? Storage::disk('public')->url($item['mockup_path']) : null,
                'elaborate_mockup' => !empty($item['elaborate_mockup']),
                'elaborate_mockup_price' => isset($item['elaborate_mockup_price']) ? (float) $item['elaborate_mockup_price'] : null,
            ];
        }

        return response()->json([
            'items' => $items,
            'total' => $total,
            'count' => array_sum(array_column($cart, 'quantity')),
            'minimum_order_amount' => self::MINIMUM_ORDER_AMOUNT,
            'meets_minimum_order' => $total >= self::MINIMUM_ORDER_AMOUNT,
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
            'print_size' => 'nullable|string|in:A0,A1,A2,A3,A4,A5,A6,Personalizat',
            'print_sides' => 'nullable|string|in:4+0,4+4,5+0,5+5',
            'format' => 'nullable|string|max:255',
            'suport' => 'nullable|string|max:100',
            'culoare' => 'nullable|string|max:100',
            'colturi' => 'nullable|string|max:100',
            'configuration_quantity' => 'nullable|integer|min:1',
        ]);

        $product = Product::with('category')->findOrFail($request->product_id);

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
            // Include toate configurațiile în key pentru a diferenția între ele
            $formatKey = $request->format ? md5($request->format) : 'no-format';
            $suportKey = $request->suport ? md5($request->suport) : 'no-suport';
            $culoareKey = $request->culoare ? md5($request->culoare) : 'no-culoare';
            $colturiKey = $request->colturi ? md5($request->colturi) : 'no-colturi';
            $cartKey = "{$product->id}_{$request->print_size}_{$request->print_sides}_{$formatKey}_{$suportKey}_{$culoareKey}_{$colturiKey}_{$request->configuration_quantity}";
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
                $query = $product->activeConfigurations()
                    ->where('print_size', $request->print_size)
                    ->where('print_sides', $request->print_sides)
                    ->where('quantity', $request->configuration_quantity);
                
                // Adaugă filtre pentru configurații dacă sunt specificate
                if ($request->has('format')) {
                    if ($request->format) {
                        $query->where('format', $request->format);
                    } else {
                        $query->whereNull('format');
                    }
                }
                
                if ($request->has('suport')) {
                    if ($request->suport) {
                        $query->where('suport', $request->suport);
                    } else {
                        $query->whereNull('suport');
                    }
                }
                
                if ($request->has('culoare')) {
                    if ($request->culoare) {
                        $query->where('culoare', $request->culoare);
                    } else {
                        $query->whereNull('culoare');
                    }
                }
                
                if ($request->has('colturi')) {
                    if ($request->colturi) {
                        $query->where('colturi', $request->colturi);
                    } else {
                        $query->whereNull('colturi');
                    }
                }
                
                $configuration = $query->first();
                
                if ($configuration) {
                    $coef = $product->category ? $product->category->getFormatPriceCoefficient($request->format ?? '') : 1.0;
                    $price = (float) $configuration->price_per_unit * $coef * (int) $request->configuration_quantity;
                }
            }
            
            $cart[$cartKey] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $price,
                'print_size' => $request->print_size,
                'print_sides' => $request->print_sides,
                'format' => $request->format,
                'suport' => $request->suport,
                'culoare' => $request->culoare,
                'colturi' => $request->colturi,
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
            'print_size' => 'nullable|string|in:A0,A1,A2,A3,A4,A5,A6,Personalizat',
            'print_sides' => 'nullable|string|in:4+0,4+4,5+0,5+5',
            'format' => 'nullable|string|max:255',
            'suport' => 'nullable|string|max:100',
            'culoare' => 'nullable|string|max:100',
            'colturi' => 'nullable|string|max:100',
            'configuration_quantity' => 'nullable|integer|min:1',
            'old_print_size' => 'nullable|string',
            'old_print_sides' => 'nullable|string',
            'old_format' => 'nullable|string|max:255',
            'old_suport' => 'nullable|string|max:100',
            'old_culoare' => 'nullable|string|max:100',
            'old_colturi' => 'nullable|string|max:100',
            'old_configuration_quantity' => 'nullable|integer',
        ]);

        $product = Product::with('category')->findOrFail($request->product_id);
        $cart = Session::get('cart', []);
        $quantity = (int) $request->quantity;

        // Găsește item-ul vechi în cart
        $oldCartKey = (string) $product->id;
        if ($request->old_print_size && $request->old_print_sides && $request->old_configuration_quantity) {
            // Construiește key-ul vechi cu toate configurațiile
            $oldFormatKey = $request->old_format ? md5($request->old_format) : 'no-format';
            $oldSuportKey = $request->old_suport ? md5($request->old_suport) : 'no-suport';
            $oldCuloareKey = $request->old_culoare ? md5($request->old_culoare) : 'no-culoare';
            $oldColturiKey = $request->old_colturi ? md5($request->old_colturi) : 'no-colturi';
            $oldCartKey = "{$product->id}_{$request->old_print_size}_{$request->old_print_sides}_{$oldFormatKey}_{$oldSuportKey}_{$oldCuloareKey}_{$oldColturiKey}_{$request->old_configuration_quantity}";
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
        $newFormat = $request->format ?? $oldItem['format'] ?? null;
        $newSuport = $request->suport ?? $oldItem['suport'] ?? null;
        $newCuloare = $request->culoare ?? $oldItem['culoare'] ?? null;
        $newColturi = $request->colturi ?? $oldItem['colturi'] ?? null;
        $newConfigQty = $request->configuration_quantity ?? $oldItem['configuration_quantity'] ?? null;

        // Verifică dacă configurația s-a schimbat
        $configChanged = ($newPrintSize != ($oldItem['print_size'] ?? null)) ||
                        ($newPrintSides != ($oldItem['print_sides'] ?? null)) ||
                        ($newFormat != ($oldItem['format'] ?? null)) ||
                        ($newSuport != ($oldItem['suport'] ?? null)) ||
                        ($newCuloare != ($oldItem['culoare'] ?? null)) ||
                        ($newColturi != ($oldItem['colturi'] ?? null)) ||
                        ($newConfigQty != ($oldItem['configuration_quantity'] ?? null));

        if ($configChanged) {
            // Șterge item-ul vechi
            unset($cart[$foundKey]);

            // Calculează noul preț
            $price = (float) $product->price;
            if ($newPrintSize && $newPrintSides && $newConfigQty) {
                $query = $product->activeConfigurations()
                    ->where('print_size', $newPrintSize)
                    ->where('print_sides', $newPrintSides)
                    ->where('quantity', $newConfigQty);
                
                // Adaugă filtre pentru configurații dacă sunt specificate
                if ($newFormat) {
                    $query->where('format', $newFormat);
                } else {
                    $query->whereNull('format');
                }
                
                if ($newSuport) {
                    $query->where('suport', $newSuport);
                } else {
                    $query->whereNull('suport');
                }
                
                if ($newCuloare) {
                    $query->where('culoare', $newCuloare);
                } else {
                    $query->whereNull('culoare');
                }
                
                if ($newColturi) {
                    $query->where('colturi', $newColturi);
                } else {
                    $query->whereNull('colturi');
                }
                
                $configuration = $query->first();
                
                if ($configuration) {
                    $coef = $product->category ? $product->category->getFormatPriceCoefficient($newFormat ?? '') : 1.0;
                    $price = (float) $configuration->price_per_unit * $coef * (int) $newConfigQty;
                }
            }

            // Creează noul key
            $newCartKey = (string) $product->id;
            if ($newPrintSize && $newPrintSides && $newConfigQty) {
                $newFormatKey = $newFormat ? md5($newFormat) : 'no-format';
                $newSuportKey = $newSuport ? md5($newSuport) : 'no-suport';
                $newCuloareKey = $newCuloare ? md5($newCuloare) : 'no-culoare';
                $newColturiKey = $newColturi ? md5($newColturi) : 'no-colturi';
                $newCartKey = "{$product->id}_{$newPrintSize}_{$newPrintSides}_{$newFormatKey}_{$newSuportKey}_{$newCuloareKey}_{$newColturiKey}_{$newConfigQty}";
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
                    'format' => $newFormat,
                    'suport' => $newSuport,
                    'culoare' => $newCuloare,
                    'colturi' => $newColturi,
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
     * Upload mockup file for a cart item. Accepts any file format.
     */
    public function uploadMockup(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'mockup' => 'required|file|max:51200', // 50 MB, any format
            'print_size' => 'nullable|string|max:50',
            'print_sides' => 'nullable|string|max:50',
            'format' => 'nullable|string|max:255',
            'suport' => 'nullable|string|max:100',
            'culoare' => 'nullable|string|max:100',
            'colturi' => 'nullable|string|max:100',
            'configuration_quantity' => 'nullable|integer|min:1',
        ], [
            'mockup.required' => 'Selectați un fișier pentru maketă.',
            'mockup.file' => 'Fișierul încărcat nu este valid.',
            'mockup.max' => 'Fișierul nu poate depăși 50 MB.',
        ]);

        $cart = Session::get('cart', []);
        $cartKey = $this->findCartKey(
            $cart,
            (int) $request->product_id,
            $request->print_size ?: null,
            $request->print_sides ?: null,
            $request->format ?: null,
            $request->suport ?: null,
            $request->culoare ?: null,
            $request->colturi ?: null,
            $request->configuration_quantity ? (int) $request->configuration_quantity : null
        );

        if ($cartKey === null) {
            return response()->json([
                'message' => 'Produsul nu se află în coș sau linia de coș nu a fost găsită. Reîncărcați pagina coșului și încercați din nou.',
            ], 404);
        }

        $file = $request->file('mockup');
        if (!$file || !$file->isValid()) {
            return response()->json([
                'message' => 'Fișierul nu a putut fi încărcat. Verificați dimensiunea (max. 50 MB) și încercați din nou.',
            ], 422);
        }
        $originalName = $file->getClientOriginalName();
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $path = $file->storeAs(
            'cart-mockups',
            date('Ymd_His') . '_' . $safeName,
            'public'
        );

        if ($path === false) {
            return response()->json(['message' => 'Eroare la încărcarea fișierului.'], 500);
        }

        if (!empty($cart[$cartKey]['mockup_path']) && Storage::disk('public')->exists($cart[$cartKey]['mockup_path'])) {
            Storage::disk('public')->delete($cart[$cartKey]['mockup_path']);
        }

        $cart[$cartKey]['mockup_path'] = $path;
        $cart[$cartKey]['mockup_filename'] = $originalName;
        Session::put('cart', $cart);

        return $this->index();
    }

    /**
     * Update "Elaborați maketa" option for a cart item.
     */
    public function updateMockupOption(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'elaborate_mockup' => 'required|boolean',
            'elaborate_mockup_price' => 'nullable|numeric|min:0',
            'print_size' => 'nullable|string|max:50',
            'print_sides' => 'nullable|string|max:50',
            'format' => 'nullable|string|max:255',
            'suport' => 'nullable|string|max:100',
            'culoare' => 'nullable|string|max:100',
            'colturi' => 'nullable|string|max:100',
            'configuration_quantity' => 'nullable|integer|min:1',
        ]);

        $cart = Session::get('cart', []);
        $cartKey = $this->findCartKey(
            $cart,
            (int) $request->product_id,
            $request->print_size ?: null,
            $request->print_sides ?: null,
            $request->format ?: null,
            $request->suport ?: null,
            $request->culoare ?: null,
            $request->colturi ?: null,
            $request->configuration_quantity ? (int) $request->configuration_quantity : null
        );

        if ($cartKey === null) {
            return response()->json(['message' => 'Produsul nu se află în coș.'], 404);
        }

        $cart[$cartKey]['elaborate_mockup'] = (bool) $request->elaborate_mockup;
        $cart[$cartKey]['elaborate_mockup_price'] = $request->elaborate_mockup
            ? (float) ($request->elaborate_mockup_price ?? 500)
            : 0;
        Session::put('cart', $cart);

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
