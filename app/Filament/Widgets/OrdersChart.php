<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersChart extends ChartWidget
{
    protected ?string $heading = 'Comenzi pe lună';

    protected function getData(): array
    {
        $months = ['Ian', 'Feb', 'Mar', 'Apr', 'Mai', 'Iun', 'Iul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $data = array_fill(0, 12, 0);

        $orders = Order::whereYear('created_at', now()->year)
            ->get()
            ->groupBy(function ($order) {
                return (int) $order->created_at->format('n');
            });

        foreach ($orders as $month => $monthOrders) {
            $data[$month - 1] = $monthOrders->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Comenzi',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

