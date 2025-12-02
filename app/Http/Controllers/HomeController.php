<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Get image URL from path.
     */
    private function getImageUrl(?string $image): string
    {
        if (empty($image)) {
            return '/images/placeholder.jpg';
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        return Storage::disk('public')->url($image);
    }

    /**
     * Display the home page.
     */
    public function index(): Response
    {
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            }])
            ->withCount('products')
            ->orderBy('sort_order')
            ->get()
            ->map(function (Category $category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'count' => $category->products_count,
                    'children' => $category->children->map(function (Category $child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'slug' => $child->slug,
                        ];
                    }),
                ];
            });

        $products = Product::where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('in_stock', 'desc') // Products in stock first
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get()
            ->map(function (Product $product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => (float) $product->price,
                    'originalPrice' => $product->original_price ? (float) $product->original_price : null,
                    'image' => $this->getImageUrl($product->image),
                    'description' => $product->short_description ?? $product->description,
                    'discount' => $product->discount,
                    'inStock' => $product->in_stock,
                ];
            });

        return Inertia::render('Home', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }
}
