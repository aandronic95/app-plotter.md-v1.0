<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DeliveryMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DeliveryMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete all existing delivery methods to avoid duplicates
        DeliveryMethod::query()->delete();

        $methods = [
            [
                'name' => 'NovaPosta',
                'slug' => 'novaposta',
                'description' => 'Livrare rapidă prin NovaPosta în toată Republica Moldova',
                'base_cost' => 50.00,
                'free_shipping_threshold' => 500.00,
                'estimated_days_min' => 2,
                'estimated_days_max' => 5,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'FANCurier',
                'slug' => 'fancurier',
                'description' => 'Livrare rapidă și sigură prin FANCurier',
                'base_cost' => 45.00,
                'free_shipping_threshold' => 500.00,
                'estimated_days_min' => 1,
                'estimated_days_max' => 3,
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($methods as $method) {
            DeliveryMethod::create($method);
        }
    }
}
