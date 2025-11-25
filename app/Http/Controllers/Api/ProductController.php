<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
     * Display a listing of products with comprehensive filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::where('is_active', true);

        // Filter by in_stock - only apply if explicitly requested
        // If not provided, show all active products (both in stock and out of stock)
        if ($request->has('in_stock') && $request->get('in_stock') === '1') {
            $query->where('in_stock', true);
        }

        // Filter by category if provided (by slug or ID)
        // Supports single category, multiple categories, and including subcategories
        if ($request->has('category') && !empty($request->category)) {
            $includeSubcategories = $request->get('include_subcategories', '1') === '1';
            $categoryParam = $request->category;
            
            // Handle array of categories
            if (is_array($categoryParam)) {
                $categoryIds = [];
                foreach ($categoryParam as $cat) {
                    if (is_numeric($cat)) {
                        $categoryIds[] = (int) $cat;
                    } else {
                        $category = Category::where('slug', $cat)
                            ->where('is_active', true)
                            ->first();
                        if ($category) {
                            $categoryIds[] = $category->id;
                            // Include subcategories if requested
                            if ($includeSubcategories) {
                                $subcategoryIds = Category::where('parent_id', $category->id)
                                    ->where('is_active', true)
                                    ->pluck('id')
                                    ->toArray();
                                $categoryIds = array_merge($categoryIds, $subcategoryIds);
                            }
                        }
                    }
                }
                if (!empty($categoryIds)) {
                    $query->whereIn('category_id', array_unique($categoryIds));
                }
            } else {
                // Single category
                $category = null;
                
                // Try to find by slug first
                if (!is_numeric($categoryParam)) {
                    $category = Category::where('slug', $categoryParam)
                        ->where('is_active', true)
                        ->first();
                }
                
                // If not found by slug, try by ID
                if (!$category && is_numeric($categoryParam)) {
                    $category = Category::where('id', (int) $categoryParam)
                        ->where('is_active', true)
                        ->first();
                }
                
                if ($category) {
                    $categoryIds = [$category->id];
                    
                    // Include subcategories if requested
                    if ($includeSubcategories) {
                        $subcategoryIds = Category::where('parent_id', $category->id)
                            ->where('is_active', true)
                            ->pluck('id')
                            ->toArray();
                        $categoryIds = array_merge($categoryIds, $subcategoryIds);
                    }
                    
                    $query->whereIn('category_id', $categoryIds);
                }
            }
        }

        // Filter by price range
        if ($request->filled('min_price') && is_numeric($request->min_price)) {
            $query->where('price', '>=', (float) $request->min_price);
        }
        if ($request->filled('max_price') && is_numeric($request->max_price)) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        // Filter by discount
        if ($request->has('has_discount') && $request->get('has_discount') === '1') {
            $query->whereNotNull('original_price')
                ->whereColumn('original_price', '>', 'price');
        }

        // Filter by discount percentage range
        if ($request->filled('min_discount') && is_numeric($request->min_discount)) {
            $minDiscount = (int) $request->min_discount;
            $query->where(function ($q) use ($minDiscount) {
                $q->where(function ($subQ) use ($minDiscount) {
                    // Products with discount_percentage column
                    $subQ->whereNotNull('discount_percentage')
                        ->where('discount_percentage', '>=', $minDiscount);
                })->orWhere(function ($subQ) use ($minDiscount) {
                    // Products with calculated discount from original_price
                    $subQ->whereNotNull('original_price')
                        ->whereColumn('original_price', '>', 'price')
                        ->whereRaw('ROUND(((original_price - price) / original_price) * 100) >= ?', [$minDiscount]);
                });
            });
        }
        if ($request->filled('max_discount') && is_numeric($request->max_discount)) {
            $maxDiscount = (int) $request->max_discount;
            $query->where(function ($q) use ($maxDiscount) {
                $q->where(function ($subQ) use ($maxDiscount) {
                    // Products with discount_percentage column
                    $subQ->whereNotNull('discount_percentage')
                        ->where('discount_percentage', '<=', $maxDiscount);
                })->orWhere(function ($subQ) use ($maxDiscount) {
                    // Products with calculated discount from original_price
                    $subQ->whereNotNull('original_price')
                        ->whereColumn('original_price', '>', 'price')
                        ->whereRaw('ROUND(((original_price - price) / original_price) * 100) <= ?', [$maxDiscount]);
                });
            });
        }

        // Filter by featured products
        if ($request->has('is_featured') && $request->get('is_featured') === '1') {
            $query->where('is_featured', true);
        }

        // Filter by new products (last 30 days)
        if ($request->has('is_new') && $request->get('is_new') === '1') {
            $query->where('created_at', '>=', now()->subDays(30));
        }

        // Filter by low stock (less than 10 items)
        if ($request->has('low_stock') && $request->get('low_stock') === '1') {
            $query->where('stock_quantity', '>', 0)
                ->where('stock_quantity', '<=', 10);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Sorting - validate sort_by to prevent SQL injection
        $allowedSortFields = ['created_at', 'price', 'name', 'sort_order', 'is_featured'];
        $sortBy = $request->get('sort_by', 'created_at');
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }
        
        $sortOrder = strtolower($request->get('sort_order', 'desc'));
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        // Apply sorting
        if ($sortBy === 'is_featured') {
            // Featured products first, then by sort_order
            $query->orderBy('is_featured', 'desc')
                ->orderBy('sort_order', 'asc')
                ->orderBy('created_at', 'desc');
        } else {
            $query->orderBy($sortBy, $sortOrder);
            // Secondary sort for consistency
            if ($sortBy !== 'sort_order') {
                $query->orderBy('sort_order', 'asc');
            }
        }

        // Pagination
        $perPage = (int) $request->get('per_page', 12);
        if ($perPage > 100) {
            $perPage = 100;
        }
        if ($perPage < 1) {
            $perPage = 12;
        }

        $products = $query->paginate($perPage)->through(function (Product $product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (float) $product->price,
                'original_price' => $product->original_price ? (float) $product->original_price : null,
                'image' => $this->getImageUrl($product->image),
                'description' => $product->short_description ?? $product->description,
                'discount' => $product->discount,
                'in_stock' => $product->in_stock,
                'stock_quantity' => $product->stock_quantity,
                'is_featured' => $product->is_featured,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                ] : null,
            ];
        });

        // Get pagination links in Laravel's standard format
        // Convert absolute URLs to relative API URLs
        $linkCollection = $products->linkCollection();
        $links = $linkCollection->map(function ($link) {
            $url = $link['url'] ?? null;
            
            // If URL is null (for disabled links), return null
            if ($url === null) {
                return [
                    'url' => null,
                    'label' => $link['label'] ?? '',
                    'active' => $link['active'] ?? false,
                ];
            }
            
            // Convert absolute URL to relative API URL
            // Extract query parameters from the full URL
            $parsedUrl = parse_url($url);
            $queryString = $parsedUrl['query'] ?? '';
            
            // Build relative API URL with query parameters
            $relativeUrl = '/api/products';
            if ($queryString) {
                $relativeUrl .= '?' . $queryString;
            }
            
            return [
                'url' => $relativeUrl,
                'label' => $link['label'] ?? '',
                'active' => $link['active'] ?? false,
            ];
        })->toArray();

        return response()->json([
            'data' => $products->items(),
            'links' => $links,
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
        ]);
    }
}

