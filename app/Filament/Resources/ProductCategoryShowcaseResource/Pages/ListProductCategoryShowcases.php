<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductCategoryShowcaseResource\Pages;

use App\Filament\Resources\ProductCategoryShowcaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductCategoryShowcases extends ListRecords
{
    protected static string $resource = ProductCategoryShowcaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

