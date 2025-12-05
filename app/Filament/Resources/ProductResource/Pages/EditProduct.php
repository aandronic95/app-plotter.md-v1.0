<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $product = $this->record;
        $tempPath = 'images/products/temp';
        $productPath = "images/products/{$product->slug}";

        // Ensure product directory exists
        if ($product->slug) {
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
        } elseif (isset($data['image']) && $data['image'] === null) {
            // Image was deleted
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = null;
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

