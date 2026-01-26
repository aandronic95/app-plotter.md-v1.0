<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'sku',
        'price',
        'original_price',
        'discount_percentage',
        'image',
        'images',
        'category_id',
        'stock_quantity',
        'in_stock',
        'is_active',
        'is_featured',
        'sort_order',
        'meta',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'discount_percentage' => 'integer',
            'images' => 'array',
            'stock_quantity' => 'integer',
            'in_stock' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'meta' => 'array',
        ];
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the order items for the product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the orders that contain this product.
     */
    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(Order::class, OrderItem::class, 'product_id', 'id', 'id', 'order_id');
    }

    /**
     * Get the wishlist items for the product.
     */
    public function wishlist(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get the configurations for the product.
     */
    public function configurations(): HasMany
    {
        return $this->hasMany(ProductConfiguration::class)->orderBy('sort_order');
    }

    /**
     * Get the active configurations for the product.
     */
    public function activeConfigurations(): HasMany
    {
        return $this->hasMany(ProductConfiguration::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /**
     * Calculate the discount percentage if original price exists.
     */
    public function getDiscountAttribute(): ?int
    {
        if ($this->original_price && $this->original_price > $this->price) {
            return (int) round((($this->original_price - $this->price) / $this->original_price) * 100);
        }

        return $this->discount_percentage;
    }

    /**
     * Check if product is on sale.
     */
    public function isOnSale(): bool
    {
        return $this->original_price !== null 
            && $this->original_price > $this->price 
            && $this->original_price > 0;
    }

    /**
     * Check if product is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return !$this->in_stock || $this->stock_quantity === 0;
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format((float) $this->price, 2, ',', '.') . ' LEI';
    }

    /**
     * Get the formatted original price.
     */
    public function getFormattedOriginalPriceAttribute(): ?string
    {
        if (!$this->original_price) {
            return null;
        }

        return number_format((float) $this->original_price, 2, ',', '.') . ' LEI';
    }

    /**
     * Get the total quantity sold.
     */
    public function getTotalSoldAttribute(): int
    {
        return $this->orderItems()->sum('quantity');
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image)) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return Storage::disk('public')->url($this->image);
    }

    /**
     * Get the images URLs array.
     */
    public function getImagesUrlsAttribute(): array
    {
        if (empty($this->images) || !is_array($this->images)) {
            return [];
        }

        return array_map(function ($image) {
            if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
                return $image;
            }

            return Storage::disk('public')->url($image);
        }, $this->images);
    }

    /**
     * Set the stock quantity and automatically update in_stock status.
     */
    public function setStockQuantityAttribute(int $value): void
    {
        $this->attributes['stock_quantity'] = $value;
        
        // Actualizează automat statusul in_stock bazat pe stock_quantity
        if ($value <= 0) {
            $this->attributes['in_stock'] = false;
        } elseif ($value > 0) {
            // Dacă stocul devine > 0, reactivează produsul
            $this->attributes['in_stock'] = true;
        }
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Actualizează automat in_stock când stock_quantity se schimbă
        static::updating(function (Product $product) {
            // Dacă stock_quantity este modificat, actualizează in_stock
            if ($product->isDirty('stock_quantity')) {
                if ($product->stock_quantity <= 0) {
                    $product->in_stock = false;
                } elseif ($product->stock_quantity > 0) {
                    $product->in_stock = true;
                }
            }
        });

        static::saved(function (Product $product) {
            $tempPath = 'images/products/temp';
            $productPath = "images/products/{$product->slug}";

            // Ensure product directory exists
            if ($product->slug) {
                Storage::disk('public')->makeDirectory($productPath);
            }

            // Move main image from temp to product folder (for both create and update)
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                $imagePath = $product->image;
                if (str_contains($imagePath, $tempPath)) {
                    $imageName = basename($imagePath);
                    $newImagePath = "{$productPath}/{$imageName}";
                    
                    if (Storage::disk('public')->move($imagePath, $newImagePath)) {
                        $product->withoutEvents(function () use ($product, $newImagePath) {
                            $product->update(['image' => $newImagePath]);
                        });
                    }
                }
            }

            // Move additional images from temp to product folder (for both create and update)
            if ($product->images && is_array($product->images)) {
                $movedImages = [];
                $needsUpdate = false;
                
                foreach ($product->images as $imagePath) {
                    if (Storage::disk('public')->exists($imagePath)) {
                        if (str_contains($imagePath, $tempPath)) {
                            $imageName = basename($imagePath);
                            $newImagePath = "{$productPath}/{$imageName}";
                            
                            if (Storage::disk('public')->move($imagePath, $newImagePath)) {
                                $movedImages[] = $newImagePath;
                                $needsUpdate = true;
                            } else {
                                $movedImages[] = $imagePath;
                            }
                        } else {
                            $movedImages[] = $imagePath;
                        }
                    } else {
                        $movedImages[] = $imagePath;
                    }
                }
                
                if ($needsUpdate) {
                    $product->withoutEvents(function () use ($product, $movedImages) {
                        $product->update(['images' => $movedImages]);
                    });
                }
            }
        });

        static::updating(function (Product $product) {
            // Move images when slug changes
            if ($product->isDirty('slug') && $product->getOriginal('slug')) {
                $oldSlug = $product->getOriginal('slug');
                $newSlug = $product->slug;
                $oldPath = "images/products/{$oldSlug}";
                $newPath = "images/products/{$newSlug}";

                // Move main image
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    $oldImagePath = $product->image;
                    if (str_contains($oldImagePath, $oldPath)) {
                        $imageName = basename($oldImagePath);
                        $newImagePath = "{$newPath}/{$imageName}";
                        
                        Storage::disk('public')->makeDirectory($newPath);
                        
                        if (Storage::disk('public')->move($oldImagePath, $newImagePath)) {
                            $product->image = $newImagePath;
                        }
                    }
                }

                // Move additional images
                if ($product->images && is_array($product->images)) {
                    $movedImages = [];
                    foreach ($product->images as $imagePath) {
                        if (Storage::disk('public')->exists($imagePath)) {
                            if (str_contains($imagePath, $oldPath)) {
                                $imageName = basename($imagePath);
                                $newImagePath = "{$newPath}/{$imageName}";
                                
                                Storage::disk('public')->makeDirectory($newPath);
                                
                                if (Storage::disk('public')->move($imagePath, $newImagePath)) {
                                    $movedImages[] = $newImagePath;
                                } else {
                                    $movedImages[] = $imagePath;
                                }
                            } else {
                                $movedImages[] = $imagePath;
                            }
                        } else {
                            $movedImages[] = $imagePath;
                        }
                    }
                    $product->images = $movedImages;
                }

                // Clean up old directory if empty
                if (Storage::disk('public')->exists($oldPath)) {
                    $files = Storage::disk('public')->files($oldPath);
                    if (empty($files)) {
                        Storage::disk('public')->deleteDirectory($oldPath);
                    }
                }
            }
        });

        static::deleting(function (Product $product) {
            // Delete product images folder when product is deleted
            $productPath = "images/products/{$product->slug}";
            
            if (Storage::disk('public')->exists($productPath)) {
                Storage::disk('public')->deleteDirectory($productPath);
            }
        });
    }
}
