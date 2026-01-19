<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomPrintBannerResource\Pages;

use App\Filament\Resources\CustomPrintBannerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomPrintBanner extends CreateRecord
{
    protected static string $resource = CustomPrintBannerResource::class;
}

