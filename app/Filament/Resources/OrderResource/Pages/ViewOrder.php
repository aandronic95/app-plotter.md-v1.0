<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record) {
            $this->record->loadMissing('orderItems');
            $data['orderItems'] = $this->record->orderItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_sku' => $item->product_sku,
                    'print_size' => $item->print_size,
                    'print_sides' => $item->print_sides,
                    'format' => $item->format,
                    'suport' => $item->suport,
                    'culoare' => $item->culoare,
                    'colturi' => $item->colturi,
                    'mockup_filename' => $item->mockup_filename,
                    'mockup_url' => $item->mockup_path ? url()->to(route('admin.order-item.download-mockup', $item)) : null,
                    'elaborate_mockup' => (bool) $item->elaborate_mockup,
                    'elaborate_mockup_price' => $item->elaborate_mockup_price ? (string) $item->elaborate_mockup_price : null,
                    'configuration_quantity' => $item->configuration_quantity,
                    'quantity' => $item->quantity,
                    'price' => (string) $item->price,
                    'subtotal' => (string) $item->subtotal,
                ];
            })->toArray();
        }
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

