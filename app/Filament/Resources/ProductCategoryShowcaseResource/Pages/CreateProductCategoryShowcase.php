<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductCategoryShowcaseResource\Pages;

use App\Filament\Resources\ProductCategoryShowcaseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductCategoryShowcase extends CreateRecord
{
    protected static string $resource = ProductCategoryShowcaseResource::class;
}

