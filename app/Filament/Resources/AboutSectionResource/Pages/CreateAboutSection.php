<?php

declare(strict_types=1);

namespace App\Filament\Resources\AboutSectionResource\Pages;

use App\Filament\Resources\AboutSectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAboutSection extends CreateRecord
{
    protected static string $resource = AboutSectionResource::class;
}

