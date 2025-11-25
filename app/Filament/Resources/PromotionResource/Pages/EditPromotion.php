<?php

declare(strict_types=1);

namespace App\Filament\Resources\PromotionResource\Pages;

use App\Filament\Resources\PromotionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditPromotion extends EditRecord
{
    protected static string $resource = PromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure at least one link type is provided
        $externalLink = $data['external_link'] ?? $this->record->external_link;
        $pageId = $data['page_id'] ?? $this->record->page_id;
        $productId = $data['product_id'] ?? $this->record->product_id;

        if (empty($externalLink) && empty($pageId) && empty($productId)) {
            throw ValidationException::withMessages([
                'external_link' => 'Trebuie să specificați fie un link extern, fie o pagină, fie un produs.',
            ]);
        }

        return $data;
    }
}

