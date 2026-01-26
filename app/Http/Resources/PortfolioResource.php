<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageUrl = null;
        if ($this->image) {
            if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
                $imageUrl = $this->image;
            } else {
                $imagePath = $this->image;
                if (str_starts_with($imagePath, 'storage/')) {
                    $imagePath = substr($imagePath, 8);
                }
                $imageUrl = asset('storage/' . $imagePath);
            }
        }

        $imagesUrls = [];
        if ($this->images && is_array($this->images)) {
            foreach ($this->images as $image) {
                if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
                    $imagesUrls[] = $image;
                } else {
                    $imagePath = $image;
                    if (str_starts_with($imagePath, 'storage/')) {
                        $imagePath = substr($imagePath, 8);
                    }
                    $imagesUrls[] = asset('storage/' . $imagePath);
                }
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $imageUrl,
            'images' => $imagesUrls,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

