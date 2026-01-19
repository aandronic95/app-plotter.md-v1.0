<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategoryShowcase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductCategoryShowcaseController extends Controller
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
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductCategoryShowcase::query();

        // Filter by active status
        if ($request->boolean('active_only')) {
            $query->active();
        }

        // Get section title and description from first record with section data
        $sectionQuery = ProductCategoryShowcase::query();
        if ($request->boolean('active_only')) {
            $sectionQuery->active();
        }
        $sectionRecord = $sectionQuery->whereNotNull('section_title')->first();
        $sectionTitle = $sectionRecord?->section_title ?? 'TIPURI DE PRODUSE';
        $sectionDescription = $sectionRecord?->section_description ?? null;
        $buttonText = $sectionRecord?->section_button_text ?? $sectionRecord?->button_text ?? 'VEZI TOT CATALOGUL >';
        $buttonLink = $sectionRecord?->section_button_link ?? $sectionRecord?->button_link ?? null;
        $carouselBannerImage = $sectionRecord?->carousel_banner_image ? $this->getImageUrl($sectionRecord->carousel_banner_image) : null;

        // Order by
        $orderBy = $request->get('order_by', 'sort_order');
        $orderDir = $request->get('order_dir', 'asc');
        $query->orderBy($orderBy, $orderDir);

        $showcases = $query->with('category')->get()->map(function (ProductCategoryShowcase $showcase) {
            return [
                'id' => $showcase->id,
                'name' => $showcase->name,
                'subtitle' => $showcase->subtitle,
                'image' => $this->getImageUrl($showcase->image),
                'category' => $showcase->category ? [
                    'id' => $showcase->category->id,
                    'name' => $showcase->category->name,
                    'slug' => $showcase->category->slug,
                ] : null,
                'button_text' => $showcase->button_text,
                'button_link' => $showcase->button_link,
            ];
        });

        return response()->json([
            'data' => $showcases,
            'section' => [
                'title' => $sectionTitle,
                'description' => $sectionDescription,
                'button_text' => $buttonText,
                'button_link' => $buttonLink,
                'carousel_banner_image' => $carouselBannerImage,
            ],
        ]);
    }
}

