<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomPrintBannerResource\Pages;

use App\Filament\Resources\CustomPrintBannerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomPrintBanner extends ViewRecord
{
    protected static string $resource = CustomPrintBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

