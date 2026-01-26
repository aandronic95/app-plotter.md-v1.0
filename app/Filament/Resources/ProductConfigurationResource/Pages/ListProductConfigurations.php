<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductConfigurationResource\Pages;

use App\Filament\Resources\ProductConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductConfigurations extends ListRecords
{
    protected static string $resource = ProductConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

