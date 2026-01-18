<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomPrintBanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomPrintBannerController extends Controller
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
        $query = CustomPrintBanner::query();

        // Filter by active status
        if ($request->boolean('active_only')) {
            $query->active();
        }

        // Order by
        $orderBy = $request->get('order_by', 'sort_order');
        $orderDir = $request->get('order_dir', 'asc');
        $query->orderBy($orderBy, $orderDir);

        // Get first active banner
        $banner = $query->first();

        if (!$banner) {
            return response()->json([
                'data' => null,
            ]);
        }

        return response()->json([
            'data' => [
                'id' => $banner->id,
                'headline' => $banner->headline,
                'title' => $banner->title,
                'description' => $banner->description,
                'button_text' => $banner->button_text,
                'button_link' => $banner->button_link,
                'image' => $this->getImageUrl($banner->image),
                'background_color' => $banner->background_color,
            ],
        ]);
    }
}

