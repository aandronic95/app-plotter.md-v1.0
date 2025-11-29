<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'subtotal',
        'tax',
        'shipping_cost',
        'total',
        'payment_status',
        'payment_method',
        'shipping_name',
        'shipping_email',
        'shipping_phone',
        'shipping_address',
        'shipping_city',
        'shipping_postal_code',
        'shipping_country',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Order $order): void {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-'.strtoupper(Str::random(8));
            }
        });

        static::updating(function (Order $order): void {
            $originalStatus = $order->getOriginal('status');
            $originalPaymentStatus = $order->getOriginal('payment_status');
            $newStatus = $order->status;
            $newPaymentStatus = $order->payment_status;

            // Verifică dacă payment_status a fost schimbat la 'paid'
            if ($order->isDirty('payment_status') && $order->payment_status === 'paid') {
                // Verifică dacă comanda are un user asociat
                if ($order->user_id && $order->user) {
                    // Calculează și adaugă punctele de fidelitate (3% din total)
                    $points = User::calculateLoyaltyPoints((float) $order->total);
                    $order->user->addLoyaltyPoints($points);
                }
            }

            // Restaurează stocul dacă comanda este anulată
            // Verifică dacă payment_status nu era deja 'refunded' pentru a evita dublarea
            if ($order->isDirty('status') && $newStatus === 'cancelled' && $originalStatus !== 'cancelled') {
                if ($originalPaymentStatus !== 'refunded') {
                    $order->restoreStock();
                }
            }

            // Restaurează stocul dacă comanda este returnată (refunded)
            // Verifică dacă statusul nu este deja 'cancelled' pentru a evita dublarea
            if ($order->isDirty('payment_status') && $newPaymentStatus === 'refunded' && $originalPaymentStatus !== 'refunded') {
                if ($originalStatus !== 'cancelled' && $newStatus !== 'cancelled') {
                    $order->restoreStock();
                }
            }

            // Dacă statusul se schimbă de la 'cancelled' sau payment_status de la 'refunded' la altceva,
            // nu trebuie să facem nimic (stocul a fost deja restaurat anterior)
        });
    }

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order items for the order.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class)->orderBy('id');
    }

    /**
     * Get the products in this order through order items.
     */
    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(Product::class, OrderItem::class, 'order_id', 'id', 'id', 'product_id');
    }

    /**
     * Get the total quantity of items in the order.
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->orderItems->sum('quantity');
    }

    /**
     * Get the number of unique products in the order.
     */
    public function getUniqueProductsCountAttribute(): int
    {
        return $this->orderItems->count();
    }

    /**
     * Check if order is paid.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if order is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if order is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get the formatted total.
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format((float) $this->total, 2, ',', '.') . ' LEI';
    }

    /**
     * Get the formatted subtotal.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return number_format((float) $this->subtotal, 2, ',', '.') . ' LEI';
    }

    /**
     * Restore stock for all products in this order.
     */
    public function restoreStock(): void
    {
        // Încarcă orderItems dacă nu sunt deja încărcate
        if (!$this->relationLoaded('orderItems')) {
            $this->load('orderItems');
        }

        foreach ($this->orderItems as $orderItem) {
            if ($orderItem->product_id) {
                $product = Product::find($orderItem->product_id);
                
                if ($product) {
                    $product->stock_quantity += $orderItem->quantity;
                    
                    // Reactivează produsul dacă stocul devine > 0
                    if ($product->stock_quantity > 0) {
                        $product->in_stock = true;
                    }
                    
                    $product->save();
                }
            }
        }
    }
}
