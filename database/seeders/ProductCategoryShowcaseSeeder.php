<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ProductCategoryShowcase;
use Illuminate\Database\Seeder;

class ProductCategoryShowcaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creează sau găsește categorii relevante pentru printare
        $categorySacose = Category::firstOrCreate(
            ['slug' => 'sacose'],
            [
                'name' => 'Sacoșe',
                'description' => 'Sacoșe personalizate pentru afacerea ta',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $categoryPachete = Category::firstOrCreate(
            ['slug' => 'pachete'],
            [
                'name' => 'Pachete',
                'description' => 'Pachete și cutii personalizate',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $categorySacoseHartie = Category::firstOrCreate(
            ['slug' => 'sacose-de-hartie'],
            [
                'name' => 'Sacoșe de hârtie',
                'description' => 'Sacoșe ecologice din hârtie',
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        $categoryHoreca = Category::firstOrCreate(
            ['slug' => 'ambalaje-horeca'],
            [
                'name' => 'Ambalaje horeca',
                'description' => 'Ambalaje pentru industria horeca',
                'is_active' => true,
                'sort_order' => 4,
            ]
        );

        // Set section title and description (stored in first record)
        ProductCategoryShowcase::create([
            'section_title' => 'TIPURI DE PRODUSE',
            'section_description' => 'Faceti ca logo-ul afacerii dvs. sa ajunga in mai multe maini, de la etichete personalizate si stickere pana la produse de branding',
            'button_text' => 'VEZI TOT CATALOGUL >',
            'button_link' => '/products',
            'name' => 'Sacoșe',
            'subtitle' => null,
            'category_id' => $categorySacose->id,
            'image' => null,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        ProductCategoryShowcase::create([
            'section_title' => null, // Will use from first record
            'section_description' => null,
            'button_text' => 'VEZI TOT CATALOGUL >',
            'button_link' => '/products',
            'name' => 'Pachete',
            'subtitle' => null,
            'category_id' => $categoryPachete->id,
            'image' => null,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        ProductCategoryShowcase::create([
            'section_title' => null,
            'section_description' => null,
            'button_text' => 'VEZI TOT CATALOGUL >',
            'button_link' => '/products',
            'name' => 'Sacoșe de hârtie',
            'subtitle' => null,
            'category_id' => $categorySacoseHartie->id,
            'image' => null,
            'sort_order' => 3,
            'is_active' => true,
        ]);

        ProductCategoryShowcase::create([
            'section_title' => null,
            'section_description' => null,
            'button_text' => 'VEZI TOT CATALOGUL >',
            'button_link' => '/products',
            'name' => 'Ambalaje horeca',
            'subtitle' => 'Sub Title',
            'category_id' => $categoryHoreca->id,
            'image' => null,
            'sort_order' => 4,
            'is_active' => true,
        ]);
    }
}
