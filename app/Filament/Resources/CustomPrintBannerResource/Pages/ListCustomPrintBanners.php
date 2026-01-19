<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomPrintBannerResource\Pages;

use App\Filament\Resources\CustomPrintBannerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomPrintBanners extends ListRecords
{
    protected static string $resource = CustomPrintBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

