<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class HeaderContact extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'phone',
        'email',
        'order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('header_contacts');
        });

        static::deleted(function () {
            Cache::forget('header_contacts');
        });
    }

    /**
     * Get all active header contacts ordered by order.
     */
    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('header_contacts', 3600, function () {
            return static::where('is_active', true)
                ->orderBy('order')
                ->orderBy('id')
                ->get();
        });
    }

    /**
     * Scope a query to only include active contacts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by order column.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('id');
    }
}
