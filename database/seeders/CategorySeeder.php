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
        // Delete all existing categories to avoid duplicates
        Category::query()->delete();

        $categories = [
            [
                'name' => 'Produse',
                'description' => 'Produse promoționale și materiale publicitare',
                'sort_order' => 1,
                'children' => [
                    [
                        'name' => 'LED',
                        'description' => 'Produse LED',
                        'sort_order' => 1,
                        'children' => [
                            ['name' => 'Lente+panouri toate marimile', 'description' => 'Lente și panouri LED în toate mărimile', 'sort_order' => 1],
                        ],
                    ],
                    [
                        'name' => 'Agende',
                        'description' => 'Agende personalizate',
                        'sort_order' => 2,
                        'children' => [
                            ['name' => 'Datate', 'description' => 'Agende cu date', 'sort_order' => 1],
                            ['name' => 'Nedatate', 'description' => 'Agende fără date', 'sort_order' => 2],
                            ['name' => 'Complecte cadou', 'description' => 'Complecte cadou cu agende', 'sort_order' => 3],
                        ],
                    ],
                    [
                        'name' => 'Carnete',
                        'description' => 'Carnete și blocnotes',
                        'sort_order' => 3,
                        'children' => [
                            ['name' => 'Prinse cu spira', 'description' => 'Carnete prinse cu spirală', 'sort_order' => 1],
                            ['name' => 'Prinse cu clei', 'description' => 'Carnete prinse cu clei', 'sort_order' => 2],
                        ],
                    ],
                    [
                        'name' => 'Calendare',
                        'description' => 'Calendare personalizate',
                        'sort_order' => 4,
                        'children' => [
                            ['name' => 'Pentru masa', 'description' => 'Calendare pentru masă', 'sort_order' => 1],
                            ['name' => 'Pentru perete', 'description' => 'Calendare pentru perete', 'sort_order' => 2],
                        ],
                    ],
                    [
                        'name' => 'Textile',
                        'description' => 'Produse textile promoționale',
                        'sort_order' => 5,
                        'children' => [
                            ['name' => 'Tricouri+chipiuri', 'description' => 'Tricouri și chipiuri', 'sort_order' => 1],
                            ['name' => 'Hanorace', 'description' => 'Hanorace personalizate', 'sort_order' => 2],
                            ['name' => 'Pungi Eco +torbe', 'description' => 'Pungi ecologice și torbe', 'sort_order' => 3],
                            ['name' => 'Veste', 'description' => 'Veste personalizate', 'sort_order' => 4],
                        ],
                    ],
                    [
                        'name' => 'Cani',
                        'description' => 'Cani personalizate',
                        'sort_order' => 6,
                    ],
                    [
                        'name' => 'Pixuri',
                        'description' => 'Pixuri promoționale',
                        'sort_order' => 7,
                        'children' => [
                            ['name' => 'Eco', 'description' => 'Pixuri ecologice', 'sort_order' => 1],
                            ['name' => 'Sneider', 'description' => 'Pixuri Sneider', 'sort_order' => 2],
                        ],
                    ],
                    [
                        'name' => 'Standuri',
                        'description' => 'Standuri expoziționale',
                        'sort_order' => 8,
                        'children' => [
                            ['name' => 'X-stand', 'description' => 'Standuri X', 'sort_order' => 1],
                            ['name' => 'Roll-up', 'description' => 'Standuri Roll-up', 'sort_order' => 2],
                            ['name' => 'A-stand+ suport drapele', 'description' => 'Standuri A și suporturi pentru drapele', 'sort_order' => 3],
                        ],
                    ],
                    [
                        'name' => 'POS-materiale',
                        'description' => 'Materiale pentru punct de vânzare',
                        'sort_order' => 9,
                        'children' => [
                            ['name' => 'Suporturi', 'description' => 'Suporturi POS', 'sort_order' => 1],
                            ['name' => 'Wobblere', 'description' => 'Wobblere promoționale', 'sort_order' => 2],
                        ],
                    ],
                    [
                        'name' => 'Cutii',
                        'description' => 'Cutii personalizate',
                        'sort_order' => 10,
                        'children' => [
                            ['name' => 'Cutii carton', 'description' => 'Cutii din carton', 'sort_order' => 1],
                            ['name' => 'Cutii PVC', 'description' => 'Cutii din PVC', 'sort_order' => 2],
                            ['name' => 'Cutii acril', 'description' => 'Cutii din acril', 'sort_order' => 3],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Servicii',
                'description' => 'Servicii de tipar și personalizare',
                'sort_order' => 2,
                'children' => [
                    [
                        'name' => 'Tipar EcoSolvent',
                        'description' => 'Servicii de tipar EcoSolvent',
                        'sort_order' => 1,
                        'children' => [
                            ['name' => 'Oracal', 'description' => 'Tipar pe Oracal', 'sort_order' => 1],
                            ['name' => 'Banner', 'description' => 'Tipar pe banner', 'sort_order' => 2],
                            ['name' => 'Panza', 'description' => 'Tipar pe pânză', 'sort_order' => 3],
                            ['name' => 'Tapete', 'description' => 'Tipar pe tapete', 'sort_order' => 4],
                            ['name' => 'OWW', 'description' => 'Tipar OWW', 'sort_order' => 5],
                        ],
                    ],
                    [
                        'name' => 'Tipar UV',
                        'description' => 'Servicii de tipar UV',
                        'sort_order' => 2,
                        'children' => [
                            ['name' => 'Stickere', 'description' => 'Tipar UV pe stickere', 'sort_order' => 1],
                            ['name' => 'Diplome', 'description' => 'Tipar UV pe diplome', 'sort_order' => 2],
                            ['name' => 'Agende', 'description' => 'Tipar UV pe agende', 'sort_order' => 3],
                            ['name' => 'Pixuri', 'description' => 'Tipar UV pe pixuri', 'sort_order' => 4],
                        ],
                    ],
                    [
                        'name' => 'Tipar DTF',
                        'description' => 'Servicii de tipar DTF',
                        'sort_order' => 3,
                        'children' => [
                            ['name' => 'Tricouri', 'description' => 'Tipar DTF pe tricouri', 'sort_order' => 1],
                            ['name' => 'Pungi', 'description' => 'Tipar DTF pe pungi', 'sort_order' => 2],
                            ['name' => 'Hanorace', 'description' => 'Tipar DTF pe hanorace', 'sort_order' => 3],
                        ],
                    ],
                    [
                        'name' => 'Personalizare',
                        'description' => 'Servicii de personalizare',
                        'sort_order' => 4,
                        'children' => [
                            ['name' => 'Agende', 'description' => 'Personalizare agende', 'sort_order' => 1],
                            ['name' => 'Termosuri', 'description' => 'Personalizare termosuri', 'sort_order' => 2],
                            ['name' => 'Cani', 'description' => 'Personalizare cani', 'sort_order' => 3],
                            ['name' => 'Umbrele', 'description' => 'Personalizare umbrele', 'sort_order' => 4],
                        ],
                    ],
                    [
                        'name' => 'Taiere freza',
                        'description' => 'Servicii de tăiere freză',
                        'sort_order' => 5,
                    ],
                    [
                        'name' => 'Taiere laser',
                        'description' => 'Servicii de tăiere laser',
                        'sort_order' => 6,
                    ],
                ],
            ],
            [
                'name' => 'Publicitate exterioara',
                'description' => 'Produse și servicii pentru publicitate exterioară',
                'sort_order' => 3,
                'children' => [
                    [
                        'name' => 'Litere volumetrice',
                        'description' => 'Litere volumetrice pentru publicitate',
                        'sort_order' => 1,
                        'children' => [
                            ['name' => 'Cu lumina', 'description' => 'Litere volumetrice cu iluminare', 'sort_order' => 1],
                            ['name' => 'Fara lumina', 'description' => 'Litere volumetrice fără iluminare', 'sort_order' => 2],
                        ],
                    ],
                    [
                        'name' => 'Caseta cu iluminare',
                        'description' => 'Casetă cu iluminare pentru publicitate',
                        'sort_order' => 2,
                        'children' => [
                            ['name' => 'Cu lumina', 'description' => 'Casetă cu iluminare', 'sort_order' => 1],
                            ['name' => 'Fara lumina', 'description' => 'Casetă fără iluminare', 'sort_order' => 2],
                        ],
                    ],
                    [
                        'name' => 'Litere NEON',
                        'description' => 'Litere neon pentru publicitate',
                        'sort_order' => 3,
                    ],
                ],
            ],
            [
                'name' => 'Utilaje',
                'description' => 'Utilaje și echipamente',
                'sort_order' => 4,
                'children' => [
                    ['name' => 'UV', 'description' => 'Utilaje tipar UV', 'sort_order' => 1],
                    ['name' => 'Solvent ecosolvent', 'description' => 'Utilaje tipar solvent ecosolvent', 'sort_order' => 2],
                    ['name' => 'Roll label printer', 'description' => 'Imprimante roll label', 'sort_order' => 3],
                ],
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
                $grandchildren = $childData['children'] ?? [];
                unset($childData['children']);

                // Create unique slug by including parent category name
                $childSlug = Str::slug($category->name . ' ' . $childData['name']);

                $child = Category::create([
                    'name' => $childData['name'],
                    'slug' => $childSlug,
                    'description' => $childData['description'] ?? null,
                    'parent_id' => $category->id,
                    'is_active' => true,
                    'sort_order' => $childData['sort_order'],
                ]);

                foreach ($grandchildren as $grandchildData) {
                    // Create unique slug by including both parent and grandparent category names
                    $grandchildSlug = Str::slug($category->name . ' ' . $child->name . ' ' . $grandchildData['name']);

                    Category::create([
                        'name' => $grandchildData['name'],
                        'slug' => $grandchildSlug,
                        'description' => $grandchildData['description'] ?? null,
                        'parent_id' => $child->id,
                        'is_active' => true,
                        'sort_order' => $grandchildData['sort_order'],
                    ]);
                }
            }
        }
    }
}
