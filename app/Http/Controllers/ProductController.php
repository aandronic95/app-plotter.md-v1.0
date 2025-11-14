<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
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
     * Display a listing of products.
     */
    public function index(Request $request): Response
    {
        $query = Product::where('is_active', true)
            ->where('in_stock', true);

        // Filter by category if provided
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate(12)->through(function (Product $product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (float) $product->price,
                'originalPrice' => $product->original_price ? (float) $product->original_price : null,
                'image' => $this->getImageUrl($product->image),
                'description' => $product->short_description ?? $product->description,
                'discount' => $product->discount,
            ];
        });

        return Inertia::render('Products/Index', [
            'products' => $products,
            'filters' => $request->only(['search', 'category', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Display the specified product.
     */
    public function show(string $slug): Response
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with('category')
            ->firstOrFail();

        $relatedProducts = Product::where('is_active', true)
            ->where('in_stock', true)
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->limit(4)
            ->get()
            ->map(function (Product $relatedProduct) {
                return [
                    'id' => $relatedProduct->id,
                    'name' => $relatedProduct->name,
                    'price' => (float) $relatedProduct->price,
                    'originalPrice' => $relatedProduct->original_price ? (float) $relatedProduct->original_price : null,
                    'image' => $this->getImageUrl($relatedProduct->image),
                    'description' => $relatedProduct->short_description ?? $relatedProduct->description,
                    'discount' => $relatedProduct->discount,
                ];
            });

        // Procesează imaginile pentru a returna URL-uri complete
        $images = [];
        if ($product->images && is_array($product->images)) {
            $images = array_map(function ($image) {
                return $this->getImageUrl($image);
            }, $product->images);
        }

        return Inertia::render('Products/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (float) $product->price,
                'originalPrice' => $product->original_price ? (float) $product->original_price : null,
                'image' => $this->getImageUrl($product->image),
                'images' => $images,
                'description' => $product->description,
                'shortDescription' => $product->short_description,
                'discount' => $product->discount,
                'stockQuantity' => $product->stock_quantity,
                'inStock' => $product->in_stock,
                'sku' => $product->sku,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                ] : null,
            ],
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
