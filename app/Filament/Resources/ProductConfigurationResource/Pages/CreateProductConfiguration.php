<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductConfigurationResource\Pages;

use App\Filament\Resources\ProductConfigurationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductConfiguration extends CreateRecord
{
    protected static string $resource = ProductConfigurationResource::class;
}

