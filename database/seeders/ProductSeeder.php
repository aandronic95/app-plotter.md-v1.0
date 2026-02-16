<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductConfiguration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete existing products and their configurations
        ProductConfiguration::query()->delete();
        Product::query()->delete();
        // Create or find categories
        $categoryCartiVizita = Category::firstOrCreate(
            ['slug' => 'carti-de-vizita'],
            [
                'name' => 'Cărți de vizită',
                'description' => 'Cărți de vizită în diferite formate și tipuri',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $categoryTiparFormatMare = Category::firstOrCreate(
            ['slug' => 'tipar-format-mare'],
            [
                'name' => 'Tipar Format Mare',
                'description' => 'Tipar în format mare pentru banner, autocolant și panouri',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $categoryProdusePromo = Category::firstOrCreate(
            ['slug' => 'produse-promo'],
            [
                'name' => 'Produse Promo',
                'description' => 'Produse promoționale personalizate',
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        $categoryAgende = Category::firstOrCreate(
            ['slug' => 'agende-personalizate'],
            [
                'name' => 'Agende Personalizate',
                'description' => 'Agende și seturi personalizate',
                'is_active' => true,
                'sort_order' => 4,
            ]
        );

        $categoryPungi = Category::firstOrCreate(
            ['slug' => 'pungi-personalizate'],
            [
                'name' => 'Pungi Personalizate',
                'description' => 'Pungi și torbe personalizate',
                'is_active' => true,
                'sort_order' => 5,
            ]
        );

        $categoryTiparSimplu = Category::firstOrCreate(
            ['slug' => 'tipar-simplu'],
            [
                'name' => 'Tipar Simplu',
                'description' => 'Servicii de tipar simplu',
                'is_active' => true,
                'sort_order' => 6,
            ]
        );

        // Cărți de vizită
        $this->createProduct('Cărți de vizită Offset', 'Cărți de vizită tipărite offset', $categoryCartiVizita->id, [
            ['format' => '90x50 mm', 'print_sides' => '4+4', 'quantity' => 500, 'price_per_unit' => 0.10],
            ['format' => '90x50 mm', 'print_sides' => '4+0', 'quantity' => 500, 'price_per_unit' => 0.08],
            ['format' => '85x55mm', 'print_sides' => '4+4', 'quantity' => 500, 'price_per_unit' => 0.10],
            ['format' => '85x55mm', 'print_sides' => '4+0', 'quantity' => 500, 'price_per_unit' => 0.08],
        ]);

        $this->createProduct('Cărți de vizită Digital', 'Cărți de vizită tipărite digital', $categoryCartiVizita->id, [
            ['format' => '90x50 mm', 'print_sides' => '4+4', 'quantity' => 500, 'price_per_unit' => 0.12],
            ['format' => '90x50 mm', 'print_sides' => '4+0', 'quantity' => 500, 'price_per_unit' => 0.10],
            ['format' => '85x55mm', 'print_sides' => '4+4', 'quantity' => 500, 'price_per_unit' => 0.12],
            ['format' => '85x55mm', 'print_sides' => '4+0', 'quantity' => 500, 'price_per_unit' => 0.10],
        ]);

        $this->createProduct('Cărți de vizită pe plastic', 'Cărți de vizită pe PVC', $categoryCartiVizita->id, [
            ['format' => '90x50 mm', 'print_sides' => '4+0', 'quantity' => 500, 'price_per_unit' => 0.25],
            ['format' => '90x50 mm', 'print_sides' => '4+4', 'quantity' => 500, 'price_per_unit' => 0.30],
            ['format' => '90x50 mm', 'print_sides' => '5+0', 'quantity' => 500, 'price_per_unit' => 0.28],
            ['format' => '90x50 mm', 'print_sides' => '5+5', 'quantity' => 500, 'price_per_unit' => 0.35],
            ['format' => '85x55mm', 'print_sides' => '4+0', 'quantity' => 500, 'price_per_unit' => 0.25],
            ['format' => '85x55mm', 'print_sides' => '4+4', 'quantity' => 500, 'price_per_unit' => 0.30],
        ]);

        $this->createProduct('Cărți de vizită Serografie', 'Cărți de vizită tipărite prin serografie', $categoryCartiVizita->id, [
            ['format' => '90x50 mm', 'print_sides' => '4+0', 'quantity' => 500, 'price_per_unit' => 0.15],
            ['format' => '90x50 mm', 'print_sides' => '4+4', 'quantity' => 500, 'price_per_unit' => 0.20],
            ['format' => '85x55mm', 'print_sides' => '4+0', 'quantity' => 500, 'price_per_unit' => 0.15],
            ['format' => '85x55mm', 'print_sides' => '4+4', 'quantity' => 500, 'price_per_unit' => 0.20],
        ]);

        $this->createProduct('Cărți de vizită UV', 'Cărți de vizită cu lac selectiv UV', $categoryCartiVizita->id, [
            ['format' => '90x50 mm', 'print_sides' => '4+4', 'quantity' => 500, 'price_per_unit' => 0.18],
            ['format' => '90x50 mm', 'print_sides' => '4+0', 'quantity' => 500, 'price_per_unit' => 0.15],
            ['format' => '85x55mm', 'print_sides' => '4+4', 'quantity' => 500, 'price_per_unit' => 0.18],
            ['format' => '85x55mm', 'print_sides' => '4+0', 'quantity' => 500, 'price_per_unit' => 0.15],
        ]);

        // Tipar Format Mare
        $this->createProduct('Banner', 'Banner în diferite formate și materiale', $categoryTiparFormatMare->id, [
            ['format' => '150x80 cm', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 150.00],
            ['format' => '150x100 cm', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 180.00],
            ['format' => '300x100 cm', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 350.00],
            ['format' => '300x200 cm', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 600.00],
            ['format' => 'Personalizat', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 0.00],
        ]);

        $this->createProduct('Autocolant Fără Tăiere', 'Autocolant fără tăiere plotter', $categoryTiparFormatMare->id, [
            ['format' => 'Personalizat', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 0.00],
        ]);

        $this->createProduct('Autocolant Tăiere Plotter', 'Autocolant cu tăiere plotter', $categoryTiparFormatMare->id, [
            ['format' => 'Personalizat', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 0.00],
        ]);

        $this->createProduct('Tipar UV pe PVC', 'Tipar UV pe PVC alb sau color', $categoryTiparFormatMare->id, [
            ['format' => 'Personalizat', 'print_sides' => '4+4', 'quantity' => 1, 'price_per_unit' => 0.00],
            ['format' => 'Personalizat', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 0.00],
            ['format' => 'Personalizat', 'print_sides' => '5+0', 'quantity' => 1, 'price_per_unit' => 0.00],
            ['format' => 'Personalizat', 'print_sides' => '5+5', 'quantity' => 1, 'price_per_unit' => 0.00],
        ]);

        $this->createProduct('Tipar UV pe Sticlă Organică', 'Tipar UV pe sticlă organică transparentă sau opal', $categoryTiparFormatMare->id, [
            ['format' => 'Personalizat', 'print_sides' => '4+4', 'quantity' => 1, 'price_per_unit' => 0.00],
            ['format' => 'Personalizat', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 0.00],
            ['format' => 'Personalizat', 'print_sides' => '5+0', 'quantity' => 1, 'price_per_unit' => 0.00],
            ['format' => 'Personalizat', 'print_sides' => '5+5', 'quantity' => 1, 'price_per_unit' => 0.00],
        ]);

        $this->createProduct('Tipar UV pe Compozit', 'Tipar UV pe compozit alb sau color', $categoryTiparFormatMare->id, [
            ['format' => 'Personalizat', 'print_sides' => '4+4', 'quantity' => 1, 'price_per_unit' => 0.00],
            ['format' => 'Personalizat', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 0.00],
            ['format' => 'Personalizat', 'print_sides' => '5+0', 'quantity' => 1, 'price_per_unit' => 0.00],
            ['format' => 'Personalizat', 'print_sides' => '5+5', 'quantity' => 1, 'price_per_unit' => 0.00],
        ]);

        // Produse Promo
        $this->createProduct('Pix Eco', 'Pix eco în diferite culori', $categoryProdusePromo->id, [
            ['format' => 'Negru', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 2.50],
            ['format' => 'Albastru', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 2.50],
            ['format' => 'Roșu', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 2.50],
            ['format' => 'Verde', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 2.50],
        ]);

        $this->createProduct('Pix Premium', 'Pix premium în diferite culori', $categoryProdusePromo->id, [
            ['format' => 'Alb', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 3.50],
            ['format' => 'Negru', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 3.50],
            ['format' => 'Albastru', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 3.50],
            ['format' => 'Roșu', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 3.50],
            ['format' => 'Verde', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 3.50],
        ]);

        $this->createProduct('Pix Metalic', 'Pix metalic în diferite culori', $categoryProdusePromo->id, [
            ['format' => 'Alb', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 4.50],
            ['format' => 'Negru', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 4.50],
            ['format' => 'Albastru', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 4.50],
            ['format' => 'Roșu', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 4.50],
            ['format' => 'Verde', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 4.50],
        ]);

        $this->createProduct('Pix Bambus', 'Pix din bambus ecologic', $categoryProdusePromo->id, [
            ['format' => 'Standard', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 5.00],
        ]);

        $this->createProduct('Creion Personalizat', 'Creion personalizat', $categoryProdusePromo->id, [
            ['format' => 'Standard', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 3.00],
        ]);

        $this->createProduct('Cani cu logo', 'Cani personalizate cu logo', $categoryProdusePromo->id, [
            ['format' => 'Alb', 'print_sides' => '4+0', 'quantity' => 50, 'price_per_unit' => 15.00],
        ]);

        $this->createProduct('Breloc', 'Breloc personalizat din plastic sau lemn', $categoryProdusePromo->id, [
            ['format' => 'Plastic', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 3.50],
            ['format' => 'Lemn', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 4.00],
        ]);

        $this->createProduct('Ecusoane Personalizate', 'Ecusoane personalizate', $categoryProdusePromo->id, [
            ['format' => 'Standard', 'print_sides' => '4+0', 'quantity' => 50, 'price_per_unit' => 5.00],
        ]);

        $this->createProduct('Badge Personalizat', 'Badge personalizat', $categoryProdusePromo->id, [
            ['format' => 'Standard', 'print_sides' => '4+0', 'quantity' => 50, 'price_per_unit' => 4.50],
        ]);

        $this->createProduct('Șiret pentru Badge', 'Șiret pentru badge în diferite culori', $categoryProdusePromo->id, [
            ['format' => 'Alb', 'print_sides' => '4+0', 'quantity' => 50, 'price_per_unit' => 2.00],
            ['format' => 'Verde', 'print_sides' => '4+0', 'quantity' => 50, 'price_per_unit' => 2.00],
            ['format' => 'Galben', 'print_sides' => '4+0', 'quantity' => 50, 'price_per_unit' => 2.00],
            ['format' => 'Albastru', 'print_sides' => '4+0', 'quantity' => 50, 'price_per_unit' => 2.00],
        ]);

        $this->createProduct('Umbrelă Simplă', 'Umbrelă simplă personalizată', $categoryProdusePromo->id, [
            ['format' => 'Alb', 'print_sides' => '4+0', 'quantity' => 50, 'price_per_unit' => 25.00],
            ['format' => 'Negru', 'print_sides' => '4+0', 'quantity' => 50, 'price_per_unit' => 25.00],
        ]);

        $this->createProduct('Umbrelă SemiAutomată', 'Umbrelă semi-automată personalizată', $categoryProdusePromo->id, [
            ['format' => 'Alb', 'print_sides' => '4+0', 'quantity' => 50, 'price_per_unit' => 35.00],
            ['format' => 'Negru', 'print_sides' => '4+0', 'quantity' => 50, 'price_per_unit' => 35.00],
        ]);

        // Agende Personalizate
        $this->createProduct('Set Agenda Premium JC1', 'Set agenda premium cu termos și pix', $categoryAgende->id, [
            ['format' => 'Negru', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 150.00],
            ['format' => 'Roșu', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 150.00],
            ['format' => 'Albastru', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 150.00],
            ['format' => 'Cafeniu', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 150.00],
        ]);

        $this->createProduct('Set Agenda Bambus JC2', 'Set agenda din bambus', $categoryAgende->id, [
            ['format' => 'Standard', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 120.00],
        ]);

        $this->createProduct('Set Agenda PRO 25K', 'Set agenda PRO 25K', $categoryAgende->id, [
            ['format' => 'Roșu', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 180.00],
            ['format' => 'Albastru', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 180.00],
            ['format' => 'Negru', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 180.00],
        ]);

        $this->createProduct('Set Agenda Standart B5 1835', 'Set agenda standard B5', $categoryAgende->id, [
            ['format' => 'Sur Deschis', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 100.00],
            ['format' => 'Sur Închis', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 100.00],
            ['format' => 'Bej', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 100.00],
        ]);

        $this->createProduct('Set Agenda Standart B5 0518', 'Set agenda standard B5', $categoryAgende->id, [
            ['format' => 'Sur', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 95.00],
            ['format' => 'Cafeniu', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 95.00],
        ]);

        $this->createProduct('Agenda B5 BOOK 9618', 'Agenda B5 BOOK', $categoryAgende->id, [
            ['format' => 'Negru', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 80.00],
            ['format' => 'Cafeniu', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 80.00],
        ]);

        $this->createProduct('Agenda B5 BOOK 18833', 'Agenda B5 BOOK', $categoryAgende->id, [
            ['format' => 'Negru', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 85.00],
            ['format' => 'Bej', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 85.00],
        ]);

        $this->createProduct('Agenda B5 BOOK 70018', 'Agenda B5 BOOK', $categoryAgende->id, [
            ['format' => 'Cafeniu', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 82.00],
            ['format' => 'Sur', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 82.00],
        ]);

        $this->createProduct('Agenda Datată A5', 'Agenda datată format A5', $categoryAgende->id, [
            ['format' => 'Albastru', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 75.00],
        ]);

        $this->createProduct('Agenda NeDatată A5', 'Agenda nedată format A5', $categoryAgende->id, [
            ['format' => 'Albastru', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 70.00],
        ]);

        // Pungi Personalizate
        $this->createProduct('Pungi Craft Standart cu Șiret', 'Pungi craft standard cu șiret', $categoryPungi->id, [
            ['format' => 'A1 - 10*36H*10', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 2.50],
            ['format' => 'A2 - 11*14H*5,5', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 2.00],
            ['format' => 'A3 - 15*20H*6', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 2.20],
            ['format' => 'A4 - 27*33H*8', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 3.50],
            ['format' => 'A5 - 32*26H*11', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 4.00],
            ['format' => 'A6 - 31,5*42H*10', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 4.50],
            ['format' => 'A7 - 38*50H*12', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 5.50],
            ['format' => 'A8 - 36*27H*9,5', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 4.20],
        ]);

        $this->createProduct('Pungi Craft Color cu Șiret', 'Pungi craft color cu șiret', $categoryPungi->id, [
            ['format' => 'BT 39 - 27*37H*9,5', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 3.50],
            ['format' => 'BT 40 - 42*28,5H*10', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 4.00],
            ['format' => 'BT 41 - 29*25H*9', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 3.20],
            ['format' => 'BT 42 - 23*18H*5,7', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 2.50],
        ]);

        $this->createProduct('Pungi Craft cu Mâner Răsucit', 'Pungi craft cu mâner răsucit', $categoryPungi->id, [
            ['format' => 'W4 - 22*27H*11', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 3.00],
            ['format' => 'W5 - 26*33H*12', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 3.50],
            ['format' => 'W6 - 31*42H*11,5', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 4.50],
            ['format' => 'W2 - 42*31H*12', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 5.00],
            ['format' => 'W3 - 44*40*13,5', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 5.50],
        ]);

        $this->createProduct('Pungi Plastificate cu Șiret Simplu', 'Pungi plastificate cu șiret simplu', $categoryPungi->id, [
            ['format' => 'L1 - 12*16H*6,5', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 2.00],
            ['format' => 'L2 - 17*24H*8', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 2.50],
            ['format' => 'L3 - 26*32H*10', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 3.50],
            ['format' => 'L4 - 30*41H*11', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 4.50],
        ]);

        $this->createProduct('Pungi Plastificate cu Șiret Silver', 'Pungi plastificate cu șiret silver', $categoryPungi->id, [
            ['format' => 'B1 - 19,5*14,5*10', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 3.00],
            ['format' => 'B2 - 25*19*9,5', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 3.50],
            ['format' => 'B3 - 25*33*11', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 4.00],
            ['format' => 'B4 - 40,5*32*16', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 5.50],
        ]);

        $this->createProduct('Torbă din IN 230g', 'Torbă din IN 230g', $categoryPungi->id, [
            ['format' => '37x41x10', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 8.00],
            ['format' => '40x30x10', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 8.50],
        ]);

        $this->createProduct('Torbă din IN 280g', 'Torbă din IN 280g', $categoryPungi->id, [
            ['format' => '37x41x10', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 10.00],
            ['format' => '40x30x10', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 10.50],
        ]);

        // Tipar Simplu
        $this->createProduct('Instrucțiuni', 'Instrucțiuni tipărite', $categoryTiparSimplu->id, [
            ['format' => 'A6', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 0.15],
            ['format' => 'A6', 'print_sides' => '4+4', 'quantity' => 100, 'price_per_unit' => 0.25],
            ['format' => 'A5', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 0.20],
            ['format' => 'A5', 'print_sides' => '4+4', 'quantity' => 100, 'price_per_unit' => 0.35],
            ['format' => 'A4', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 0.30],
            ['format' => 'A4', 'print_sides' => '4+4', 'quantity' => 100, 'price_per_unit' => 0.50],
            ['format' => 'A3', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 0.60],
            ['format' => 'A3', 'print_sides' => '4+4', 'quantity' => 100, 'price_per_unit' => 1.00],
        ]);

        $this->createProduct('Flayere', 'Flayere tipărite', $categoryTiparSimplu->id, [
            ['format' => 'A6', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 0.20],
            ['format' => 'A6', 'print_sides' => '4+4', 'quantity' => 100, 'price_per_unit' => 0.35],
            ['format' => 'A5', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 0.30],
            ['format' => 'A5', 'print_sides' => '4+4', 'quantity' => 100, 'price_per_unit' => 0.50],
            ['format' => 'A4', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 0.40],
            ['format' => 'A4', 'print_sides' => '4+4', 'quantity' => 100, 'price_per_unit' => 0.70],
            ['format' => 'Euro', 'print_sides' => '4+0', 'quantity' => 100, 'price_per_unit' => 0.35],
            ['format' => 'Euro', 'print_sides' => '4+4', 'quantity' => 100, 'price_per_unit' => 0.60],
        ]);

        $this->createProduct('Postere', 'Postere tipărite', $categoryTiparSimplu->id, [
            ['format' => 'A4', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 5.00],
            ['format' => 'A3', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 8.00],
            ['format' => 'A2', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 15.00],
            ['format' => 'A1', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 25.00],
            ['format' => 'A0', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 40.00],
            ['format' => 'Personalizat', 'print_sides' => '4+0', 'quantity' => 1, 'price_per_unit' => 0.00],
        ]);

        $this->createProduct('Mape cu Buzunar Lipit', 'Mape cu buzunar lipit', $categoryTiparSimplu->id, [
            ['format' => 'A4', 'print_sides' => '4+0', 'quantity' => 50, 'price_per_unit' => 3.50],
            ['format' => 'A4', 'print_sides' => '4+4', 'quantity' => 50, 'price_per_unit' => 5.00],
        ]);

        $this->createProduct('Mape Tăiate cu Cuțitul', 'Mape tăiate cu cuțitul', $categoryTiparSimplu->id, [
            ['format' => 'A4', 'print_sides' => '4+0', 'quantity' => 50, 'price_per_unit' => 4.00],
            ['format' => 'A4', 'print_sides' => '4+4', 'quantity' => 50, 'price_per_unit' => 5.50],
        ]);

        $this->command->info('Produse create cu succes!');
    }

    /**
     * Create a product with configurations
     */
    private function createProduct(string $name, string $description, int $categoryId, array $configurations): void
    {
        $slug = Str::slug($name);
        
        // Delete existing product if it exists
        $existingProduct = Product::where('slug', $slug)->first();
        if ($existingProduct) {
            $existingProduct->configurations()->delete();
            $existingProduct->delete();
        }
        
        $product = Product::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $description,
            'short_description' => substr($description, 0, 150),
            'price' => 0, // Will be set from configurations
            'category_id' => $categoryId,
            'stock_quantity' => 999,
            'in_stock' => true,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 0,
        ]);

        $sortOrder = 0;
        foreach ($configurations as $config) {
            $quantity = $config['quantity'] ?? 500;
            $pricePerUnit = $config['price_per_unit'] ?? 0.10;
            $price = $quantity * $pricePerUnit;

            // Determine print_size from format if not provided
            $printSize = $config['print_size'] ?? null;
            if (!$printSize && isset($config['format'])) {
                // Try to extract A-size from format
                if (preg_match('/\bA[0-6]\b/i', $config['format'], $matches)) {
                    $printSize = strtoupper($matches[0]);
                } else {
                    $printSize = 'Personalizat';
                }
            }
            $printSize = $printSize ?? 'A4';

            ProductConfiguration::create([
                'product_id' => $product->id,
                'print_size' => $printSize,
                'print_sides' => $config['print_sides'] ?? '4+0',
                'format' => $config['format'] ?? null,
                'quantity' => $quantity,
                'price' => $price,
                'price_per_unit' => $pricePerUnit,
                'production_days' => 5,
                'is_active' => true,
                'sort_order' => $sortOrder++,
            ]);
        }

        // Update product price to minimum configuration price
        $minPrice = $product->configurations()->min('price');
        if ($minPrice) {
            $product->update(['price' => $minPrice]);
        }
    }
}
