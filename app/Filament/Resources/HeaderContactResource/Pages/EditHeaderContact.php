<?php

declare(strict_types=1);

namespace App\Filament\Resources\HeaderContactResource\Pages;

use App\Filament\Resources\HeaderContactResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHeaderContact extends EditRecord
{
    protected static string $resource = HeaderContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

