<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductConfiguration extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'print_size',
        'print_sides',
        'format',
        'suport',
        'culoare',
        'colturi',
        'quantity',
        'price',
        'price_per_unit',
        'production_days',
        'is_active',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price' => 'decimal:2',
            'price_per_unit' => 'decimal:2',
            'production_days' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the product that owns the configuration.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the production date.
     */
    public function getProductionDateAttribute(): \Carbon\Carbon
    {
        return now()->addDays($this->production_days);
    }

    /**
     * Get formatted production date.
     */
    public function getFormattedProductionDateAttribute(): string
    {
        return $this->production_date->format('d.m.Y');
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format((float) $this->price, 2, ',', '.') . ' LEI';
    }

    /**
     * Get formatted price per unit.
     */
    public function getFormattedPricePerUnitAttribute(): string
    {
        return number_format((float) $this->price_per_unit, 2, ',', '.') . ' LEI';
    }

    /**
     * Scope a query to only include active configurations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by print size.
     */
    public function scopeByPrintSize($query, string $size)
    {
        return $query->where('print_size', $size);
    }

    /**
     * Scope a query to filter by print sides.
     */
    public function scopeByPrintSides($query, string $sides)
    {
        return $query->where('print_sides', $sides);
    }

    /**
     * Scope a query to filter by quantity.
     */
    public function scopeByQuantity($query, int $quantity)
    {
        return $query->where('quantity', $quantity);
    }
}
