<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DeliveryAddress;
use App\Models\Order;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
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
     * Show the user's profile page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        $orders = Order::where('user_id', $user->id)
            ->with('orderItems')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $deliveryAddresses = $user->deliveryAddresses()->get();

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
            ->filter()
            ->values();

        return Inertia::render('Profile', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'loyalty_points' => $user->loyalty_points,
            ],
            'deliveryAddresses' => $deliveryAddresses->map(function (DeliveryAddress $address) {
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
            }),
            'orders' => $orders->through(function (Order $order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'total' => (float) $order->total,
                    'created_at' => $order->created_at->format('d.m.Y H:i'),
                    'items_count' => $order->orderItems->sum('quantity'),
                ];
            }),
            'wishlist' => $wishlistItems,
        ]);
    }

    /**
     * Store a new delivery address.
     */
    public function storeAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'country' => 'required|string|max:255',
            'is_default' => 'boolean',
        ]);

        $user = $request->user();
        
        // Dacă este prima adresă sau este setată ca default, o facem default
        if ($user->deliveryAddresses()->count() === 0 || ($validated['is_default'] ?? false)) {
            $validated['is_default'] = true;
        }

        $user->deliveryAddresses()->create($validated);

        return redirect()->route('profile')
            ->with('success', 'Adresa de livrare a fost adăugată cu succes!');
    }

    /**
     * Update a delivery address.
     */
    public function updateAddress(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'country' => 'required|string|max:255',
            'is_default' => 'boolean',
        ]);

        $address = DeliveryAddress::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $address->update($validated);

        return redirect()->route('profile')
            ->with('success', 'Adresa de livrare a fost actualizată cu succes!');
    }

    /**
     * Delete a delivery address.
     */
    public function deleteAddress(Request $request, int $id): RedirectResponse
    {
        $address = DeliveryAddress::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $address->delete();

        return redirect()->route('profile')
            ->with('success', 'Adresa de livrare a fost ștearsă cu succes!');
    }

    /**
     * Set an address as default.
     */
    public function setDefaultAddress(Request $request, int $id): RedirectResponse
    {
        $address = DeliveryAddress::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $address->update(['is_default' => true]);

        return redirect()->route('profile')
            ->with('success', 'Adresa a fost setată ca adresă implicită!');
    }
}
