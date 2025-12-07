<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Generate sitemap XML.
     */
    public function index(): Response
    {
        $baseUrl = config('app.url');
        
        $sitemap = Cache::remember('sitemap', 3600, function () use ($baseUrl) {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
            $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

            // Homepage
            $xml .= $this->url($baseUrl, now(), 'daily', '1.0');
            $xml .= $this->url($baseUrl . '/products', now(), 'daily', '0.9');
            $xml .= $this->url($baseUrl . '/categories', now(), 'daily', '0.9');
            $xml .= $this->url($baseUrl . '/promotions', now(), 'daily', '0.8');
            $xml .= $this->url($baseUrl . '/pages', now(), 'weekly', '0.7');

            // Products
            $products = Product::where('is_active', true)
                ->orderBy('updated_at', 'desc')
                ->get();

            foreach ($products as $product) {
                $url = $baseUrl . '/products/' . $product->slug;
                $lastmod = $product->updated_at ? $product->updated_at->toAtomString() : now()->toAtomString();
                $xml .= $this->url($url, $lastmod, 'weekly', '0.8', $product);
            }

            // Categories
            $categories = Category::where('is_active', true)
                ->orderBy('updated_at', 'desc')
                ->get();

            foreach ($categories as $category) {
                $url = $baseUrl . '/categories/' . $category->slug;
                $lastmod = $category->updated_at ? $category->updated_at->toAtomString() : now()->toAtomString();
                $xml .= $this->url($url, $lastmod, 'weekly', '0.7');
            }

            // Pages
            $pages = Page::published()
                ->orderBy('updated_at', 'desc')
                ->get();

            foreach ($pages as $page) {
                $url = $baseUrl . '/' . $page->slug;
                $lastmod = $page->updated_at ? $page->updated_at->toAtomString() : now()->toAtomString();
                $xml .= $this->url($url, $lastmod, 'monthly', '0.6');
            }

            $xml .= '</urlset>';

            return $xml;
        });

        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    /**
     * Generate URL entry for sitemap.
     */
    private function url(string $loc, string|object $lastmod, string $changefreq, string $priority, ?Product $product = null): string
    {
        $xml = "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . "</loc>\n";

        if (is_object($lastmod)) {
            $lastmod = $lastmod->toAtomString();
        }

        $xml .= "    <lastmod>" . htmlspecialchars($lastmod, ENT_XML1, 'UTF-8') . "</lastmod>\n";
        $xml .= "    <changefreq>" . htmlspecialchars($changefreq, ENT_XML1, 'UTF-8') . "</changefreq>\n";
        $xml .= "    <priority>" . htmlspecialchars($priority, ENT_XML1, 'UTF-8') . "</priority>\n";

        // Add image if product has image
        if ($product && $product->image) {
            $imageUrl = str_starts_with($product->image, 'http') 
                ? $product->image 
                : config('app.url') . '/storage/' . $product->image;
            
            $xml .= "    <image:image>\n";
            $xml .= "      <image:loc>" . htmlspecialchars($imageUrl, ENT_XML1, 'UTF-8') . "</image:loc>\n";
            if ($product->name) {
                $xml .= "      <image:title>" . htmlspecialchars($product->name, ENT_XML1, 'UTF-8') . "</image:title>\n";
            }
            $xml .= "    </image:image>\n";
        }

        $xml .= "  </url>\n";

        return $xml;
    }
}
