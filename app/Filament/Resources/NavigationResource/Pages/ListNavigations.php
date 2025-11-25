<?php

namespace App\Filament\Resources\NavigationResource\Pages;

use App\Filament\Resources\NavigationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListNavigations extends ListRecords
{
    protected static string $resource = NavigationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Toate')
                ->badge(fn () => \App\Models\Navigation::count()),
            'main' => Tab::make('Principal')
                ->badge(fn () => \App\Models\Navigation::where('group', 'main')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('group', 'main')),
            'footer' => Tab::make('Footer')
                ->badge(fn () => \App\Models\Navigation::where('group', 'footer')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('group', 'footer')),
            'header' => Tab::make('Header')
                ->badge(fn () => \App\Models\Navigation::where('group', 'header')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('group', 'header')),
        ];
    }
}

