<?php

declare(strict_types=1);

namespace App\Filament\Resources\PromotionResource\Pages;

use App\Filament\Resources\PromotionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreatePromotion extends CreateRecord
{
    protected static string $resource = PromotionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure at least one link type is provided
        if (empty($data['external_link']) && empty($data['page_id']) && empty($data['product_id'])) {
            throw ValidationException::withMessages([
                'external_link' => 'Trebuie să specificați fie un link extern, fie o pagină, fie un produs.',
            ]);
        }

        return $data;
    }
}

