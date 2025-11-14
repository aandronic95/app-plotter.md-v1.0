<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total produse', Product::count())
                ->description('Produse active: ' . Product::where('is_active', true)->count())
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5]),

            Stat::make('Total comenzi', Order::count())
                ->description('Comenzi noi: ' . Order::where('status', 'pending')->count())
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info')
                ->chart([3, 2, 4, 5, 3, 4, 6]),

            Stat::make('Total utilizatori', User::count())
                ->description('Utilizatori noi: ' . User::whereDate('created_at', '>=', now()->subDays(7))->count() . ' (ultimele 7 zile)')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning')
                ->chart([2, 3, 2, 4, 3, 2, 3]),

            Stat::make('Total categorii', Category::count())
                ->description('Categorii active: ' . Category::where('is_active', true)->count())
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary')
                ->chart([1, 2, 1, 3, 2, 1, 2]),
        ];
    }
}

