<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DeliveryMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Show checkout page.
     */
    public function checkout(): Response|\Illuminate\Http\RedirectResponse
    {
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index');
        }

        $items = [];
        $subtotal = 0;

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

            $itemSubtotal = (float) $item['price'] * $item['quantity'];
            $subtotal += $itemSubtotal;

            $items[] = [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (float) $item['price'],
                'image' => $product->image ?? '/images/placeholder.jpg',
                'quantity' => $item['quantity'],
                'subtotal' => $itemSubtotal,
                'print_size' => $item['print_size'] ?? null,
                'print_sides' => $item['print_sides'] ?? null,
                'format' => $item['format'] ?? null,
                'suport' => $item['suport'] ?? null,
                'culoare' => $item['culoare'] ?? null,
                'colturi' => $item['colturi'] ?? null,
                'configuration_quantity' => $item['configuration_quantity'] ?? null,
            ];
        }

        $tax = $subtotal * 0.19; // TVA 19%
        
        // Obține metodele de livrare active
        $deliveryMethods = DeliveryMethod::active()
            ->orderBy('sort_order')
            ->get()
            ->map(function ($method) use ($subtotal) {
                return [
                    'id' => $method->id,
                    'name' => $method->name,
                    'slug' => $method->slug,
                    'description' => $method->description,
                    'logo' => $method->logo ? asset('storage/' . $method->logo) : null,
                    'base_cost' => (float) $method->base_cost,
                    'free_shipping_threshold' => $method->free_shipping_threshold ? (float) $method->free_shipping_threshold : null,
                    'estimated_days_min' => $method->estimated_days_min,
                    'estimated_days_max' => $method->estimated_days_max,
                    'calculated_cost' => $method->calculateShippingCost($subtotal),
                ];
            });

        // Calculăm costul de livrare implicit (prima metodă activă sau 0)
        $defaultShippingCost = $deliveryMethods->isNotEmpty() 
            ? $deliveryMethods->first()['calculated_cost'] 
            : ($subtotal > 500 ? 0 : 50);
        
        $total = $subtotal + $tax + $defaultShippingCost;

        // Obține adresele salvate ale utilizatorului (dacă este autentificat)
        $deliveryAddresses = [];
        if (auth()->check()) {
            $deliveryAddresses = auth()->user()->deliveryAddresses()->get()->map(function ($address) {
                return [
                    'id' => $address->id,
                    'name' => $address->name,
                    'phone' => $address->phone,
                    'address' => $address->address,
                    'city' => $address->city,
                    'postal_code' => $address->postal_code,
                    'country' => $address->country,
                    'is_default' => $address->is_default,
                    'full_address' => $address->full_address,
                ];
            });
        }

        return Inertia::render('Checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shippingCost' => $defaultShippingCost,
            'total' => $total,
            'deliveryAddresses' => $deliveryAddresses,
            'deliveryMethods' => $deliveryMethods,
        ]);
    }

    /**
     * Store order.
     */
    public function store(Request $request): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'delivery_method_id' => 'required|exists:delivery_methods,id',
            'shipping_name' => 'required|string|max:255',
            'shipping_email' => 'required|email|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'shipping_city' => 'required|string|max:255',
            'shipping_postal_code' => 'nullable|string|max:10',
            'shipping_country' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Coșul este gol.');
        }

        DB::beginTransaction();

        try {
            $items = [];
            $subtotal = 0;

            // Verifică disponibilitatea produselor
            foreach ($cart as $cartKey => $item) {
                // Pentru compatibilitate cu items vechi (fără product_id) și items noi (cu key compus)
                $productId = null;
                
                if (isset($item['product_id'])) {
                    $productId = $item['product_id'];
                } elseif (is_numeric($cartKey)) {
                    // Key simplu (pentru items vechi)
                    $productId = (int) $cartKey;
                } elseif (is_string($cartKey) && str_contains($cartKey, '_')) {
                    // Key compus: productId_size_sides_quantity
                    $parts = explode('_', $cartKey);
                    $productId = isset($parts[0]) && is_numeric($parts[0]) ? (int) $parts[0] : null;
                }
                
                if (!$productId) {
                    DB::rollBack();
                    return redirect()->route('cart.index')
                        ->with('error', 'Eroare: produs invalid în coș. Te rugăm să golești coșul și să încerci din nou.');
                }
                
                $product = Product::find($productId);
                if (!$product || !$product->is_active || !$product->in_stock) {
                    DB::rollBack();
                    return redirect()->route('cart.index')
                        ->with('error', 'Unul sau mai multe produse nu mai sunt disponibile.');
                }

                if ($item['quantity'] > $product->stock_quantity) {
                    DB::rollBack();
                    return redirect()->route('cart.index')
                        ->with('error', "Produsul '{$product->name}' nu are stoc suficient.");
                }

                // Pentru configurații, calculează subtotalul corect
                $itemSubtotal = (float) $item['price'] * $item['quantity'];
                
                // Dacă există configurație, folosește prețul total din configurație
                if (isset($item['print_size']) && isset($item['print_sides']) && isset($item['configuration_quantity'])) {
                    $query = $product->activeConfigurations()
                        ->where('print_size', $item['print_size'])
                        ->where('print_sides', $item['print_sides'])
                        ->where('quantity', $item['configuration_quantity']);
                    
                    // Adaugă filtre pentru configurații suplimentare dacă există
                    if (isset($item['format']) && $item['format']) {
                        $query->where('format', $item['format']);
                    } else {
                        $query->where(function($q) {
                            $q->whereNull('format')->orWhere('format', '');
                        });
                    }
                    
                    if (isset($item['suport']) && $item['suport']) {
                        $query->where('suport', $item['suport']);
                    } else {
                        $query->where(function($q) {
                            $q->whereNull('suport')->orWhere('suport', '');
                        });
                    }
                    
                    if (isset($item['culoare']) && $item['culoare']) {
                        $query->where('culoare', $item['culoare']);
                    } else {
                        $query->where(function($q) {
                            $q->whereNull('culoare')->orWhere('culoare', '');
                        });
                    }
                    
                    if (isset($item['colturi']) && $item['colturi']) {
                        $query->where('colturi', $item['colturi']);
                    } else {
                        $query->where(function($q) {
                            $q->whereNull('colturi')->orWhere('colturi', '');
                        });
                    }
                    
                    $configuration = $query->first();
                    
                    if ($configuration) {
                        // Pentru configurații, subtotalul este prețul total din configurație
                        $itemSubtotal = (float) $configuration->price;
                    }
                }
                
                $subtotal += $itemSubtotal;

            $items[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'price' => (float) $item['price'],
                'print_size' => $item['print_size'] ?? null,
                'print_sides' => $item['print_sides'] ?? null,
                'format' => $item['format'] ?? null,
                'suport' => $item['suport'] ?? null,
                'culoare' => $item['culoare'] ?? null,
                'colturi' => $item['colturi'] ?? null,
                'configuration_quantity' => $item['configuration_quantity'] ?? null,
            ];
            }

            $tax = $subtotal * 0.19;
            
            // Obține metoda de livrare selectată și calculează costul
            $deliveryMethod = DeliveryMethod::findOrFail($request->delivery_method_id);
            $shippingCost = $deliveryMethod->calculateShippingCost($subtotal);
            $total = $subtotal + $tax + $shippingCost;

            // Creează comanda
            $order = Order::create([
                'user_id' => auth()->id(),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'payment_status' => 'pending',
                'delivery_method_id' => $request->delivery_method_id,
                'shipping_name' => $request->shipping_name,
                'shipping_email' => $request->shipping_email,
                'shipping_phone' => $request->shipping_phone,
                'shipping_address' => $request->shipping_address,
                'shipping_city' => $request->shipping_city,
                'shipping_postal_code' => $request->shipping_postal_code,
                'shipping_country' => $request->shipping_country ?? 'Republica Moldova',
                'notes' => $request->notes,
            ]);

            // Creează articolele comenzii și actualizează stocul
            foreach ($items as $item) {
                // Dacă există configurație, folosește prețul total din configurație
                $price = (float) $item['price'];
                $subtotal = $price * $item['quantity'];
                
                if ($item['print_size'] && $item['print_sides'] && $item['configuration_quantity']) {
                    $query = $item['product']->activeConfigurations()
                        ->where('print_size', $item['print_size'])
                        ->where('print_sides', $item['print_sides'])
                        ->where('quantity', $item['configuration_quantity']);
                    
                    // Adaugă filtre pentru configurații suplimentare dacă există
                    if (isset($item['format']) && $item['format']) {
                        $query->where('format', $item['format']);
                    } else {
                        $query->where(function($q) {
                            $q->whereNull('format')->orWhere('format', '');
                        });
                    }
                    
                    if (isset($item['suport']) && $item['suport']) {
                        $query->where('suport', $item['suport']);
                    } else {
                        $query->where(function($q) {
                            $q->whereNull('suport')->orWhere('suport', '');
                        });
                    }
                    
                    if (isset($item['culoare']) && $item['culoare']) {
                        $query->where('culoare', $item['culoare']);
                    } else {
                        $query->where(function($q) {
                            $q->whereNull('culoare')->orWhere('culoare', '');
                        });
                    }
                    
                    if (isset($item['colturi']) && $item['colturi']) {
                        $query->where('colturi', $item['colturi']);
                    } else {
                        $query->where(function($q) {
                            $q->whereNull('colturi')->orWhere('colturi', '');
                        });
                    }
                    
                    $configuration = $query->first();
                    
                    if ($configuration) {
                        // Pentru configurații, prețul per bucată este price_per_unit
                        $price = (float) $configuration->price_per_unit;
                        // Subtotalul este prețul total din configurație (nu price_per_unit * quantity)
                        // Deoarece quantity din item este de obicei 1 (când se comandă o configurație)
                        $subtotal = (float) $configuration->price;
                    }
                }
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'product_sku' => $item['product']->sku,
                    'print_size' => $item['print_size'] ?? null,
                    'print_sides' => $item['print_sides'] ?? null,
                    'format' => $item['format'] ?? null,
                    'suport' => $item['suport'] ?? null,
                    'culoare' => $item['culoare'] ?? null,
                    'colturi' => $item['colturi'] ?? null,
                    'configuration_quantity' => $item['configuration_quantity'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                // Actualizează stocul (in_stock se actualizează automat prin mutator/observer)
                $item['product']->stock_quantity -= $item['quantity'];
                $item['product']->save();
            }

            DB::commit();

            // Golește coșul
            Session::forget('cart');

            // Set flash message before redirect
            Session::flash('success', 'Comanda a fost plasată cu succes!');

            // Use Inertia::location() for redirect to work with Inertia
            return Inertia::location(route('orders.show', $order->id));
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Set flash message before redirect
            Session::flash('error', 'A apărut o eroare la procesarea comenzii. Te rugăm să încerci din nou.');
            
            // Use Inertia::location() for redirect to work with Inertia
            return Inertia::location(route('checkout'));
        }
    }

    /**
     * Show order details.
     */
    public function show(int $id): Response
    {
        $order = Order::with(['orderItems.product', 'deliveryMethod'])
            ->where('id', $id)
            ->where(function ($query) {
                $query->where('user_id', auth()->id())
                    ->orWhereNull('user_id');
            })
            ->firstOrFail();

        return Inertia::render('Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'subtotal' => (float) $order->subtotal,
                'tax' => (float) $order->tax,
                'shipping_cost' => (float) $order->shipping_cost,
                'total' => (float) $order->total,
                'delivery_method' => $order->deliveryMethod ? [
                    'id' => $order->deliveryMethod->id,
                    'name' => $order->deliveryMethod->name,
                    'logo' => $order->deliveryMethod->logo ? asset('storage/' . $order->deliveryMethod->logo) : null,
                ] : null,
                'delivery_tracking_number' => $order->delivery_tracking_number,
                'shipping_name' => $order->shipping_name,
                'shipping_email' => $order->shipping_email,
                'shipping_phone' => $order->shipping_phone,
                'shipping_address' => $order->shipping_address,
                'shipping_city' => $order->shipping_city,
                'shipping_postal_code' => $order->shipping_postal_code,
                'shipping_country' => $order->shipping_country,
                'notes' => $order->notes,
                'created_at' => $order->created_at->format('d.m.Y H:i'),
                'items' => $order->orderItems->map(function (OrderItem $item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product_name,
                        'product_sku' => $item->product_sku,
                        'print_size' => $item->print_size,
                        'print_sides' => $item->print_sides,
                        'format' => $item->format,
                        'suport' => $item->suport,
                        'culoare' => $item->culoare,
                        'colturi' => $item->colturi,
                        'configuration_quantity' => $item->configuration_quantity,
                        'quantity' => $item->quantity,
                        'price' => (float) $item->price,
                        'subtotal' => (float) $item->subtotal,
                    ];
                }),
            ],
        ]);
    }
}
