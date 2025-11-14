<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronice',
                'description' => 'Produse electronice și gadget-uri',
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Telefoane', 'description' => 'Smartphone-uri și telefoane mobile', 'sort_order' => 1],
                    ['name' => 'Laptopuri', 'description' => 'Laptopuri și notebook-uri', 'sort_order' => 2],
                    ['name' => 'Tablete', 'description' => 'Tablete și iPad-uri', 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Îmbrăcăminte',
                'description' => 'Haine și accesorii de modă',
                'sort_order' => 2,
                'children' => [
                    ['name' => 'Bărbați', 'description' => 'Îmbrăcăminte pentru bărbați', 'sort_order' => 1],
                    ['name' => 'Femei', 'description' => 'Îmbrăcăminte pentru femei', 'sort_order' => 2],
                    ['name' => 'Copii', 'description' => 'Îmbrăcăminte pentru copii', 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Casa & Bucătărie',
                'description' => 'Produse pentru casă și bucătărie',
                'sort_order' => 3,
                'children' => [
                    ['name' => 'Electrocasnice', 'description' => 'Aparate electrocasnice', 'sort_order' => 1],
                    ['name' => 'Ustensile', 'description' => 'Ustensile de bucătărie', 'sort_order' => 2],
                    ['name' => 'Decor', 'description' => 'Articole de decor pentru casă', 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Sport & Fitness',
                'description' => 'Echipament sportiv și fitness',
                'sort_order' => 4,
                'children' => [
                    ['name' => 'Echipament Fitness', 'description' => 'Echipament pentru fitness', 'sort_order' => 1],
                    ['name' => 'Îmbrăcăminte Sport', 'description' => 'Haine sportive', 'sort_order' => 2],
                    ['name' => 'Accesorii Sport', 'description' => 'Accesorii pentru sport', 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Jucării',
                'description' => 'Jucării pentru copii',
                'sort_order' => 5,
            ],
            [
                'name' => 'Frumusețe',
                'description' => 'Produse de frumusețe și cosmetice',
                'sort_order' => 6,
            ],
            [
                'name' => 'Auto',
                'description' => 'Accesorii și piese auto',
                'sort_order' => 7,
            ],
            [
                'name' => 'Cărți',
                'description' => 'Cărți și materiale de lectură',
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $category = Category::create([
                'name' => $categoryData['name'],
                'slug' => Str::slug($categoryData['name']),
                'description' => $categoryData['description'],
                'is_active' => true,
                'sort_order' => $categoryData['sort_order'],
            ]);

            foreach ($children as $childData) {
                Category::create([
                    'name' => $childData['name'],
                    'slug' => Str::slug($childData['name']),
                    'description' => $childData['description'],
                    'parent_id' => $category->id,
                    'is_active' => true,
                    'sort_order' => $childData['sort_order'],
                ]);
            }
        }
    }
}
