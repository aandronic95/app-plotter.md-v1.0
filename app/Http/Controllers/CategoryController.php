<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
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
     * Map category with nested children recursively.
     */
    private function mapCategoryWithNestedChildren(Category $category, bool $includeDetails = false): array
    {
        $result = [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
        ];

        if ($includeDetails) {
            $result['description'] = $category->description;
            $result['image'] = $this->getImageUrl($category->image);
            $result['products_count'] = $category->active_products_count ?? $category->products_count ?? 0;
        } else {
            $result['count'] = $category->products_count ?? 0;
        }

        if ($category->relationLoaded('children') && $category->children->isNotEmpty()) {
            // Load nested children if not already loaded
            if (!$category->children->first()->relationLoaded('children')) {
                $category->load(['children.children' => function ($query) {
                    $query->where('is_active', true)->orderBy('sort_order');
                }]);
            }

            $result['children'] = $category->children->map(function (Category $child) use ($includeDetails) {
                return $this->mapCategoryWithNestedChildren($child, $includeDetails);
            })->toArray();
        } else {
            $result['children'] = [];
        }

        return $result;
    }

    /**
     * Display a listing of all categories with their subcategories.
     */
    public function index(): Response
    {
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order')
                    ->withCount('activeProducts');
            }])
            ->with(['children.children' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order')
                    ->withCount('activeProducts');
            }])
            ->with(['children.children.children' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order')
                    ->withCount('activeProducts');
            }])
            ->withCount('activeProducts')
            ->orderBy('sort_order')
            ->get()
            ->map(function (Category $category) {
                return $this->mapCategoryWithNestedChildren($category, true);
            });

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Display products for a specific category.
     */
    public function show(string $slug, Request $request): Response
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->with(['parent', 'children'])
            ->firstOrFail();

        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            }])
            ->with(['children.children' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            }])
            ->with(['children.children.children' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            }])
            ->withCount('products')
            ->orderBy('sort_order')
            ->get()
            ->map(function (Category $cat) {
                return $this->mapCategoryWithNestedChildren($cat, false);
            });

        $query = Product::where('is_active', true)
            ->where('category_id', $category->id);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Always put in-stock products first, then out-of-stock at the end
        $query->orderBy('in_stock', 'desc') // Products in stock first
            ->orderBy($sortBy, $sortOrder);

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

        return Inertia::render('Categories/Show', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => $category->image,
                'parent' => $category->parent ? [
                    'id' => $category->parent->id,
                    'name' => $category->parent->name,
                    'slug' => $category->parent->slug,
                ] : null,
                'children' => $category->children->map(function (Category $child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'slug' => $child->slug,
                    ];
                }),
            ],
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'sort_by', 'sort_order']),
        ]);
    }
}
