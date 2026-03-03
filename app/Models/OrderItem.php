<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'print_size',
        'print_sides',
        'format',
        'suport',
        'culoare',
        'colturi',
        'mockup_path',
        'mockup_filename',
        'elaborate_mockup',
        'elaborate_mockup_price',
        'configuration_quantity',
        'quantity',
        'price',
        'subtotal',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'configuration_quantity' => 'integer',
            'quantity' => 'integer',
            'elaborate_mockup' => 'boolean',
            'elaborate_mockup_price' => 'decimal:2',
            'price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (OrderItem $orderItem): void {
            $orderItem->subtotal = (float) $orderItem->price * (int) $orderItem->quantity;
            if (!empty($orderItem->elaborate_mockup) && $orderItem->elaborate_mockup_price > 0) {
                $orderItem->subtotal += (float) $orderItem->elaborate_mockup_price;
            }
        });
    }

    /**
     * Get the order that owns the order item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product that owns the order item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withDefault([
            'name' => $this->product_name ?? 'Produs șters',
            'slug' => null,
            'price' => $this->price ?? 0,
        ]);
    }

    /**
     * Check if the product still exists.
     */
    public function hasProduct(): bool
    {
        return $this->product_id !== null && $this->product !== null && $this->product->exists;
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format((float) $this->price, 2, ',', '.') . ' LEI';
    }

    /**
     * Get the formatted subtotal.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return number_format((float) $this->subtotal, 2, ',', '.') . ' LEI';
    }
}
