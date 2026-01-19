<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductCategoryShowcaseResource\Pages;

use App\Filament\Resources\ProductCategoryShowcaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductCategoryShowcase extends ViewRecord
{
    protected static string $resource = ProductCategoryShowcaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

