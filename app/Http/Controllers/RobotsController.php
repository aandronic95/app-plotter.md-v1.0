<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Generate robots.txt dynamically.
     */
    public function index(): Response
    {
        $siteUrl = config('app.url');
        
        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /adminer/\n";
        $content .= "Disallow: /dashboard/\n";
        $content .= "Disallow: /profile/\n";
        $content .= "Disallow: /cart/\n";
        $content .= "Disallow: /checkout/\n";
        $content .= "Disallow: /orders/\n";
        $content .= "Disallow: /api/\n";
        $content .= "\n";
        $content .= "# Sitemap\n";
        $content .= "Sitemap: {$siteUrl}/sitemap.xml\n";

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}

