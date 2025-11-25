<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'site_name',
        'site_description',
        'site_logo',
        'site_logo_icon',
        'site_favicon',
        'site_email',
        'site_phone',
        'site_address',
        'site_facebook',
        'site_instagram',
        'site_twitter',
        'site_linkedin',
        'site_meta_keywords',
        'site_meta_description',
        'site_google_analytics',
    ];

    /**
     * Get the current site settings (singleton pattern).
     */
    public static function current(): self
    {
        return Cache::remember('site_settings', 3600, function () {
            return static::firstOrCreate(['id' => 1]);
        });
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('site_settings');
        });

        static::deleted(function () {
            Cache::forget('site_settings');
        });
    }

    /**
     * Get logo URL.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->site_logo) {
            return null;
        }

        if (str_starts_with($this->site_logo, 'http://') || str_starts_with($this->site_logo, 'https://')) {
            return $this->site_logo;
        }

        return asset('storage/' . $this->site_logo);
    }

    /**
     * Get logo icon URL.
     */
    public function getLogoIconUrlAttribute(): ?string
    {
        if (!$this->site_logo_icon) {
            return null;
        }

        if (str_starts_with($this->site_logo_icon, 'http://') || str_starts_with($this->site_logo_icon, 'https://')) {
            return $this->site_logo_icon;
        }

        return asset('storage/' . $this->site_logo_icon);
    }

    /**
     * Get favicon URL.
     */
    public function getFaviconUrlAttribute(): ?string
    {
        if (!$this->site_favicon) {
            return null;
        }

        if (str_starts_with($this->site_favicon, 'http://') || str_starts_with($this->site_favicon, 'https://')) {
            return $this->site_favicon;
        }

        return asset('storage/' . $this->site_favicon);
    }
}
