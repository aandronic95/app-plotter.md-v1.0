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
        'show_login_modal',
        'show_site_name',
        'show_logo',
        'show_loyalty_points',
        'show_newsletter_form',
        'loading_text_main',
        'loading_text_sub',
        'header_contact_1_phone',
        'header_contact_1_email',
        'header_contact_2_phone',
        'header_contact_2_email',
        'header_contact_3_phone',
        'header_contact_3_email',
        'service_feature_1_icon',
        'service_feature_1_title',
        'service_feature_1_description',
        'service_feature_2_icon',
        'service_feature_2_title',
        'service_feature_2_description',
        'service_feature_3_icon',
        'service_feature_3_title',
        'service_feature_3_description',
        'service_feature_4_icon',
        'service_feature_4_title',
        'service_feature_4_description',
        'hero_banner_headline',
        'hero_banner_title',
        'hero_banner_description',
        'hero_banner_features',
        'hero_banner_button1_text',
        'hero_banner_button1_link',
        'hero_banner_button2_text',
        'hero_banner_button2_link',
        'hero_banner_image',
        'hero_banner_is_active',
        'hero_banner_sort_order',
        'hero_banner_rotating_words',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'show_login_modal' => 'boolean',
        'show_site_name' => 'boolean',
        'show_logo' => 'boolean',
        'show_loyalty_points' => 'boolean',
        'show_newsletter_form' => 'boolean',
        'hero_banner_features' => 'array',
        'hero_banner_is_active' => 'boolean',
        'hero_banner_sort_order' => 'integer',
        'hero_banner_rotating_words' => 'array',
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

    /**
     * Get hero banner image URL.
     */
    public function getHeroBannerImageUrlAttribute(): ?string
    {
        if (!$this->hero_banner_image) {
            return null;
        }

        if (str_starts_with($this->hero_banner_image, 'http://') || str_starts_with($this->hero_banner_image, 'https://')) {
            return $this->hero_banner_image;
        }

        return asset('storage/' . $this->hero_banner_image);
    }

    /**
     * Get hero banner features as simple array (for API/frontend).
     */
    public function getHeroBannerFeaturesArrayAttribute(): array
    {
        $features = $this->hero_banner_features ?? [];
        
        // Transform array of objects to simple array of strings
        if (!empty($features) && is_array($features)) {
            $firstItem = reset($features);
            // If it's an array of objects with 'feature' key
            if (is_array($firstItem) && isset($firstItem['feature'])) {
                return array_column($features, 'feature');
            }
            // If it's already a simple array
            if (is_string($firstItem)) {
                return $features;
            }
        }
        
        return [];
    }

    /**
     * Get hero banner rotating words as simple array (for API/frontend).
     */
    public function getHeroBannerRotatingWordsArrayAttribute(): array
    {
        $words = $this->hero_banner_rotating_words ?? [];
        
        // Transform array of objects to simple array of strings
        if (!empty($words) && is_array($words)) {
            $firstItem = reset($words);
            // If it's an array of objects with 'word' key
            if (is_array($firstItem) && isset($firstItem['word'])) {
                return array_column($words, 'word');
            }
            // If it's already a simple array
            if (is_string($firstItem)) {
                return $words;
            }
        }
        
        return [];
    }
}
