<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductConfiguration;
use Illuminate\Database\Seeder;

class ProductConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Găsește produse existente sau creează unul de test pentru configurații
        $products = Product::where('is_active', true)->get();

        if ($products->isEmpty()) {
            $this->command->warn('Nu există produse active. Rulează mai întâi ProductSeeder!');
            return;
        }

        // Configurații exemplu pentru primul produs
        $firstProduct = $products->first();

        $configurations = [
            // A3, 2-sided (4+4)
            [
                'product_id' => $firstProduct->id,
                'print_size' => 'A3',
                'print_sides' => '4+4',
                'quantity' => 500,
                'price' => 10900.00,
                'price_per_unit' => 21.80,
                'production_days' => 5,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'product_id' => $firstProduct->id,
                'print_size' => 'A3',
                'print_sides' => '4+4',
                'quantity' => 1000,
                'price' => 11800.00,
                'price_per_unit' => 11.80,
                'production_days' => 5,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'product_id' => $firstProduct->id,
                'print_size' => 'A3',
                'print_sides' => '4+4',
                'quantity' => 2000,
                'price' => 23000.00,
                'price_per_unit' => 11.50,
                'production_days' => 7,
                'is_active' => true,
                'sort_order' => 3,
            ],
            // A3, 1-sided (4+0)
            [
                'product_id' => $firstProduct->id,
                'print_size' => 'A3',
                'print_sides' => '4+0',
                'quantity' => 500,
                'price' => 6500.00,
                'price_per_unit' => 13.00,
                'production_days' => 4,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'product_id' => $firstProduct->id,
                'print_size' => 'A3',
                'print_sides' => '4+0',
                'quantity' => 1000,
                'price' => 7000.00,
                'price_per_unit' => 7.00,
                'production_days' => 4,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'product_id' => $firstProduct->id,
                'print_size' => 'A3',
                'print_sides' => '4+0',
                'quantity' => 2000,
                'price' => 13000.00,
                'price_per_unit' => 6.50,
                'production_days' => 6,
                'is_active' => true,
                'sort_order' => 6,
            ],
            // A4, 2-sided (4+4)
            [
                'product_id' => $firstProduct->id,
                'print_size' => 'A4',
                'print_sides' => '4+4',
                'quantity' => 500,
                'price' => 5500.00,
                'price_per_unit' => 11.00,
                'production_days' => 4,
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'product_id' => $firstProduct->id,
                'print_size' => 'A4',
                'print_sides' => '4+4',
                'quantity' => 1000,
                'price' => 6000.00,
                'price_per_unit' => 6.00,
                'production_days' => 4,
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'product_id' => $firstProduct->id,
                'print_size' => 'A4',
                'print_sides' => '4+4',
                'quantity' => 2000,
                'price' => 11000.00,
                'price_per_unit' => 5.50,
                'production_days' => 6,
                'is_active' => true,
                'sort_order' => 9,
            ],
            // A4, 1-sided (4+0)
            [
                'product_id' => $firstProduct->id,
                'print_size' => 'A4',
                'print_sides' => '4+0',
                'quantity' => 500,
                'price' => 3500.00,
                'price_per_unit' => 7.00,
                'production_days' => 3,
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'product_id' => $firstProduct->id,
                'print_size' => 'A4',
                'print_sides' => '4+0',
                'quantity' => 1000,
                'price' => 3800.00,
                'price_per_unit' => 3.80,
                'production_days' => 3,
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'product_id' => $firstProduct->id,
                'print_size' => 'A4',
                'print_sides' => '4+0',
                'quantity' => 2000,
                'price' => 7000.00,
                'price_per_unit' => 3.50,
                'production_days' => 5,
                'is_active' => true,
                'sort_order' => 12,
            ],
        ];

        foreach ($configurations as $configData) {
            ProductConfiguration::create($configData);
        }

        // Adaugă configurații pentru alte produse dacă există
        if ($products->count() > 1) {
            $secondProduct = $products->skip(1)->first();
            
            // Configurații simplificate pentru al doilea produs
            $additionalConfigs = [
                [
                    'product_id' => $secondProduct->id,
                    'print_size' => 'A4',
                    'print_sides' => '4+4',
                    'quantity' => 500,
                    'price' => 6000.00,
                    'price_per_unit' => 12.00,
                    'production_days' => 5,
                    'is_active' => true,
                    'sort_order' => 1,
                ],
                [
                    'product_id' => $secondProduct->id,
                    'print_size' => 'A4',
                    'print_sides' => '4+4',
                    'quantity' => 1000,
                    'price' => 6500.00,
                    'price_per_unit' => 6.50,
                    'production_days' => 5,
                    'is_active' => true,
                    'sort_order' => 2,
                ],
                [
                    'product_id' => $secondProduct->id,
                    'print_size' => 'A4',
                    'print_sides' => '4+4',
                    'quantity' => 2000,
                    'price' => 12000.00,
                    'price_per_unit' => 6.00,
                    'production_days' => 7,
                    'is_active' => true,
                    'sort_order' => 3,
                ],
            ];

            foreach ($additionalConfigs as $configData) {
                ProductConfiguration::create($configData);
            }
        }

        $this->command->info('Configurații produse create cu succes!');
    }
}
