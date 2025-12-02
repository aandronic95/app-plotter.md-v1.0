<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
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
        $query = Product::where('is_active', true);

        // Filter by in_stock - only apply if explicitly requested
        // If not provided, show all active products (both in stock and out of stock)
        if ($request->has('in_stock') && $request->get('in_stock') === '1') {
            $query->where('in_stock', true);
        }

        // Filter by category if provided (by slug)
        // Includes subcategories by default
        if ($request->has('category') && !empty($request->category)) {
            $includeSubcategories = $request->get('include_subcategories', '1') === '1';
            $category = Category::where('slug', $request->category)
                ->where('is_active', true)
                ->first();
            
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

        // Filter by price range
        if ($request->has('min_price') && is_numeric($request->min_price)) {
            $query->where('price', '>=', (float) $request->min_price);
        }
        if ($request->has('max_price') && is_numeric($request->max_price)) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        // Filter by discount
        if ($request->has('has_discount') && $request->get('has_discount') === '1') {
            $query->whereNotNull('original_price')
                ->whereColumn('original_price', '>', 'price');
        }

        // Filter by discount percentage range
        if ($request->has('min_discount') && is_numeric($request->min_discount)) {
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
        if ($request->has('max_discount') && is_numeric($request->max_discount)) {
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
        
        // Apply both min and max discount filters together if both are provided
        // This ensures products match both criteria
        if ($request->has('min_discount') && is_numeric($request->min_discount) 
            && $request->has('max_discount') && is_numeric($request->max_discount)) {
            // The filters above already handle this, but we ensure they work together
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
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
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

        // Apply sorting - always put in-stock products first, then out-of-stock at the end
        if ($sortBy === 'is_featured') {
            // Featured products first, then by in_stock, then by sort_order
            $query->orderBy('is_featured', 'desc')
                ->orderBy('in_stock', 'desc') // Products in stock first
                ->orderBy('sort_order', 'asc')
                ->orderBy('created_at', 'desc');
        } else {
            // Always sort by in_stock first (unless explicitly sorting by in_stock)
            if ($sortBy !== 'in_stock') {
                $query->orderBy('in_stock', 'desc'); // Products in stock first
            }
            // Then sort by the requested field
            $query->orderBy($sortBy, $sortOrder);
            // Secondary sort for consistency
            if ($sortBy !== 'sort_order') {
                $query->orderBy('sort_order', 'asc');
            }
        }

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
                'inStock' => $product->in_stock,
            ];
        });

        // Fetch categories for sidebar
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            }])
            ->withCount('products')
            ->orderBy('sort_order')
            ->get()
            ->map(function (Category $cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'count' => $cat->products_count ?? 0,
                    'children' => $cat->children ? $cat->children->map(function (Category $child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'slug' => $child->slug,
                        ];
                    })->toArray() : [],
                ];
            });

        // Calculate price range for all active products (respecting current filters except price)
        $priceRangeQuery = Product::where('is_active', true);
        
        // Apply same filters as main query (except price filters) for accurate price range
        if ($request->has('in_stock') && $request->get('in_stock') === '1') {
            $priceRangeQuery->where('in_stock', true);
        }
        
        if ($request->has('category') && !empty($request->category)) {
            $includeSubcategories = $request->get('include_subcategories', '1') === '1';
            $category = Category::where('slug', $request->category)
                ->where('is_active', true)
                ->first();
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
                
                $priceRangeQuery->whereIn('category_id', $categoryIds);
            }
        }
        
        if ($request->has('has_discount') && $request->get('has_discount') === '1') {
            $priceRangeQuery->whereNotNull('original_price')
                ->whereColumn('original_price', '>', 'price');
        }
        
        if ($request->has('is_featured') && $request->get('is_featured') === '1') {
            $priceRangeQuery->where('is_featured', true);
        }
        
        if ($request->has('is_new') && $request->get('is_new') === '1') {
            $priceRangeQuery->where('created_at', '>=', now()->subDays(30));
        }
        
        if ($request->has('low_stock') && $request->get('low_stock') === '1') {
            $priceRangeQuery->where('stock_quantity', '>', 0)
                ->where('stock_quantity', '<=', 10);
        }
        
        $priceRange = $priceRangeQuery
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'priceRange' => [
                'min' => $priceRange->min_price ? (float) $priceRange->min_price : 0,
                'max' => $priceRange->max_price ? (float) $priceRange->max_price : 10000,
            ],
            'filters' => $request->only(['search', 'category', 'min_price', 'max_price', 'in_stock', 'has_discount', 'is_featured', 'is_new', 'low_stock', 'min_discount', 'max_discount', 'sort_by', 'sort_order']),
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
                    'slug' => $relatedProduct->slug,
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
