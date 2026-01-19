<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class HeroBannerResource extends JsonResource
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
                // Use asset() for consistency with SiteSetting model
                // Remove 'storage/' prefix if already present to avoid duplication
                $imagePath = $this->image;
                if (str_starts_with($imagePath, 'storage/')) {
                    $imagePath = substr($imagePath, 8); // Remove 'storage/' prefix
                }
                $imageUrl = asset('storage/' . $imagePath);
            }
        }

        return [
            'id' => $this->id,
            'headline' => $this->headline,
            'title' => $this->title,
            'description' => $this->description,
            'features' => $this->features ?? [],
            'button1_text' => $this->button1_text,
            'button1_link' => $this->button1_link,
            'button2_text' => $this->button2_text,
            'button2_link' => $this->button2_link,
            'image' => $imageUrl,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'rotating_words' => $this->rotating_words ?? ['HAINE', 'CĂRȚI DE VIZITE', 'BANERE', 'CUTII', 'POSTERE'],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
