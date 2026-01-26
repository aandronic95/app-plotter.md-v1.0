<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductConfigurationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'print_size' => $this->print_size,
            'print_sides' => $this->print_sides,
            'quantity' => $this->quantity,
            'price' => (float) $this->price,
            'price_per_unit' => (float) $this->price_per_unit,
            'production_days' => $this->production_days,
            'production_date' => $this->formatted_production_date,
            'production_date_raw' => $this->production_date->format('Y-m-d'),
            'formatted_price' => $this->formatted_price,
            'formatted_price_per_unit' => $this->formatted_price_per_unit,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];
    }
}
