<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tempPath = 'images/products/temp';
        $productPath = "images/products/{$data['slug']}";

        // Ensure product directory exists
        if (isset($data['slug']) && $data['slug']) {
            Storage::disk('public')->makeDirectory($productPath);
        }

        // Handle main image - move from temp if needed
        if (isset($data['image']) && $data['image']) {
            $imagePath = $data['image'];
            if (is_string($imagePath) && str_contains($imagePath, $tempPath)) {
                $imageName = basename($imagePath);
                $newImagePath = "{$productPath}/{$imageName}";
                
                if (Storage::disk('public')->exists($imagePath)) {
                    if (Storage::disk('public')->move($imagePath, $newImagePath)) {
                        $data['image'] = $newImagePath;
                    }
                }
            }
        }

        // Handle additional images - move from temp if needed
        if (isset($data['images']) && is_array($data['images'])) {
            $movedImages = [];
            foreach ($data['images'] as $imagePath) {
                if (is_string($imagePath) && str_contains($imagePath, $tempPath)) {
                    $imageName = basename($imagePath);
                    $newImagePath = "{$productPath}/{$imageName}";
                    
                    if (Storage::disk('public')->exists($imagePath)) {
                        if (Storage::disk('public')->move($imagePath, $newImagePath)) {
                            $movedImages[] = $newImagePath;
                        } else {
                            $movedImages[] = $imagePath;
                        }
                    } else {
                        $movedImages[] = $imagePath;
                    }
                } else {
                    $movedImages[] = $imagePath;
                }
            }
            $data['images'] = $movedImages;
        }

        return $data;
    }
}

