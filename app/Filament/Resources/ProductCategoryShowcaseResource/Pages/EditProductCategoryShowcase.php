<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductCategoryShowcaseResource\Pages;

use App\Filament\Resources\ProductCategoryShowcaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductCategoryShowcase extends EditRecord
{
    protected static string $resource = ProductCategoryShowcaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

