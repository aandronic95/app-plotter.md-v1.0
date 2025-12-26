<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class SiteSettingController extends Controller
{
    /**
     * Get current site settings.
     */
    public function index(): JsonResponse
    {
        $settings = SiteSetting::current();

        return response()->json([
            'data' => [
                'site_name' => $settings->site_name,
                'site_description' => $settings->site_description,
                'site_logo' => $settings->logo_url,
                'site_logo_icon' => $settings->logo_icon_url,
                'site_favicon' => $settings->favicon_url,
                'site_email' => $settings->site_email,
                'site_phone' => $settings->site_phone,
                'site_address' => $settings->site_address,
                'site_facebook' => $settings->site_facebook,
                'site_instagram' => $settings->site_instagram,
                'site_twitter' => $settings->site_twitter,
                'site_linkedin' => $settings->site_linkedin,
                'site_meta_keywords' => $settings->site_meta_keywords,
                'site_meta_description' => $settings->site_meta_description,
                'site_google_analytics' => $settings->site_google_analytics,
                'show_login_modal' => $settings->show_login_modal ?? true,
                'show_site_name' => $settings->show_site_name ?? true,
                'show_logo' => $settings->show_logo ?? true,
                'show_loyalty_points' => $settings->show_loyalty_points ?? true,
                'show_newsletter_form' => $settings->show_newsletter_form ?? true,
                'loading_text_main' => $settings->loading_text_main ?? 'tanavius',
                'loading_text_sub' => $settings->loading_text_sub ?? 'www.plotter.md',
                'header_contact_1_phone' => $settings->header_contact_1_phone,
                'header_contact_1_email' => $settings->header_contact_1_email,
                'header_contact_2_phone' => $settings->header_contact_2_phone,
                'header_contact_2_email' => $settings->header_contact_2_email,
                'header_contact_3_phone' => $settings->header_contact_3_phone,
                'header_contact_3_email' => $settings->header_contact_3_email,
            ],
        ]);
    }
}
