<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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
    public function checkout(): Response
    {
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index');
        }

        $items = [];
        $subtotal = 0;

        foreach ($cart as $productId => $item) {
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
            ];
        }

        $tax = $subtotal * 0.19; // TVA 19%
        $shippingCost = $subtotal > 500 ? 0 : 50; // Transport gratuit peste 500 lei
        $total = $subtotal + $tax + $shippingCost;

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
            'shippingCost' => $shippingCost,
            'total' => $total,
            'deliveryAddresses' => $deliveryAddresses,
        ]);
    }

    /**
     * Store order.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
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
            foreach ($cart as $productId => $item) {
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

                $itemSubtotal = (float) $item['price'] * $item['quantity'];
                $subtotal += $itemSubtotal;

                $items[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => (float) $item['price'],
                ];
            }

            $tax = $subtotal * 0.19;
            $shippingCost = $subtotal > 500 ? 0 : 50;
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
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'product_sku' => $item['product']->sku,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);

                // Actualizează stocul
                $item['product']->stock_quantity -= $item['quantity'];
                if ($item['product']->stock_quantity <= 0) {
                    $item['product']->in_stock = false;
                }
                $item['product']->save();
            }

            DB::commit();

            // Golește coșul
            Session::forget('cart');

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Comanda a fost plasată cu succes!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('checkout')
                ->with('error', 'A apărut o eroare la procesarea comenzii. Te rugăm să încerci din nou.');
        }
    }

    /**
     * Show order details.
     */
    public function show(int $id): Response
    {
        $order = Order::with('orderItems.product')
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
                        'quantity' => $item->quantity,
                        'price' => (float) $item->price,
                        'subtotal' => (float) $item->subtotal,
                    ];
                }),
            ],
        ]);
    }
}
