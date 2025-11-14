<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::whereNull('parent_id')->get();

        if ($categories->isEmpty()) {
            $this->command->warn('Nu există categorii. Rulează mai întâi CategorySeeder!');
            return;
        }

        $products = [
            // Electronice
            [
                'name' => 'Smartphone Samsung Galaxy S24',
                'description' => 'Smartphone premium cu ecran AMOLED de 6.2", procesor octa-core, 8GB RAM, 128GB stocare, camera triplă 50MP.',
                'short_description' => 'Smartphone premium cu ecran AMOLED și camera triplă 50MP',
                'price' => 2999.99,
                'original_price' => 3499.99,
                'stock_quantity' => 15,
                'sku' => 'SM-GAL-S24-001',
                'is_featured' => true,
                'category_name' => 'Electronice',
            ],
            [
                'name' => 'Laptop Dell XPS 15',
                'description' => 'Laptop profesional cu procesor Intel Core i7, 16GB RAM, SSD 512GB, ecran 15.6" Full HD, placa video dedicată.',
                'short_description' => 'Laptop profesional cu procesor Intel Core i7 și SSD 512GB',
                'price' => 5999.99,
                'original_price' => 6999.99,
                'stock_quantity' => 8,
                'sku' => 'DELL-XPS15-001',
                'is_featured' => true,
                'category_name' => 'Electronice',
            ],
            [
                'name' => 'Tabletă iPad Air',
                'description' => 'Tabletă Apple iPad Air cu ecran Retina de 10.9", procesor M1, 64GB stocare, suport pentru Apple Pencil.',
                'short_description' => 'Tabletă Apple cu procesor M1 și ecran Retina',
                'price' => 3499.99,
                'original_price' => 3999.99,
                'stock_quantity' => 12,
                'sku' => 'APPLE-IPAD-AIR-001',
                'is_featured' => false,
                'category_name' => 'Electronice',
            ],
            [
                'name' => 'Căști Wireless Sony WH-1000XM5',
                'description' => 'Căști over-ear cu cancelare activă a zgomotului, Bluetooth 5.2, autonomie 30 ore, microfon integrat.',
                'short_description' => 'Căști premium cu cancelare activă a zgomotului',
                'price' => 1499.99,
                'original_price' => 1799.99,
                'stock_quantity' => 20,
                'sku' => 'SONY-WH1000XM5-001',
                'is_featured' => false,
                'category_name' => 'Electronice',
            ],

            // Îmbrăcăminte
            [
                'name' => 'Tricou Premium Bărbați',
                'description' => 'Tricou din bumbac 100% organic, croială modernă, disponibil în multiple culori, mărimi S-XXL.',
                'short_description' => 'Tricou premium din bumbac 100% organic',
                'price' => 89.99,
                'original_price' => 129.99,
                'stock_quantity' => 50,
                'sku' => 'CLOTH-TEE-M-001',
                'is_featured' => false,
                'category_name' => 'Îmbrăcăminte',
            ],
            [
                'name' => 'Jeans Slim Fit Femei',
                'description' => 'Jeans slim fit pentru femei, material stretch, croială modernă, multiple mărimi disponibile.',
                'short_description' => 'Jeans slim fit cu material stretch',
                'price' => 199.99,
                'original_price' => 249.99,
                'stock_quantity' => 35,
                'sku' => 'CLOTH-JEANS-F-001',
                'is_featured' => false,
                'category_name' => 'Îmbrăcăminte',
            ],
            [
                'name' => 'Geacă Iarnă Copii',
                'description' => 'Geacă de iarnă pentru copii, impermeabilă, cu glugă, izolație termică, mărimi 4-14 ani.',
                'short_description' => 'Geacă de iarnă impermeabilă pentru copii',
                'price' => 299.99,
                'original_price' => 399.99,
                'stock_quantity' => 25,
                'sku' => 'CLOTH-JACKET-K-001',
                'is_featured' => false,
                'category_name' => 'Îmbrăcăminte',
            ],

            // Casa & Bucătărie
            [
                'name' => 'Set Tigaie Antiaderentă',
                'description' => 'Set de 3 tigăi antiaderente, mărimi diferite, mânere ergonomice, compatibile cu toate tipurile de aragaz.',
                'short_description' => 'Set de 3 tigăi antiaderente premium',
                'price' => 349.99,
                'original_price' => 449.99,
                'stock_quantity' => 30,
                'sku' => 'HOME-PAN-SET-001',
                'is_featured' => false,
                'category_name' => 'Casa & Bucătărie',
            ],
            [
                'name' => 'Robot Aspirator Xiaomi',
                'description' => 'Robot aspirator inteligent cu control prin aplicație, aspirare puternică, programare automată, baterie de lungă durată.',
                'short_description' => 'Robot aspirator inteligent cu control prin aplicație',
                'price' => 1299.99,
                'original_price' => 1599.99,
                'stock_quantity' => 15,
                'sku' => 'HOME-ROBOT-XIAOMI-001',
                'is_featured' => true,
                'category_name' => 'Casa & Bucătărie',
            ],
            [
                'name' => 'Set Veselă Porțelan',
                'description' => 'Set complet de veselă din porțelan premium, 12 persoane, design elegant, rezistent la spălare în mașină.',
                'short_description' => 'Set complet de veselă din porțelan premium',
                'price' => 599.99,
                'original_price' => 799.99,
                'stock_quantity' => 10,
                'sku' => 'HOME-DISH-SET-001',
                'is_featured' => false,
                'category_name' => 'Casa & Bucătărie',
            ],

            // Sport & Fitness
            [
                'name' => 'Bandă de Alergat Fitness',
                'description' => 'Bandă de alergat profesională, motor 2.5HP, viteză maximă 16 km/h, ecran LCD, programe preinstalate.',
                'short_description' => 'Bandă de alergat profesională cu motor 2.5HP',
                'price' => 3999.99,
                'original_price' => 4999.99,
                'stock_quantity' => 5,
                'sku' => 'SPORT-TREADMILL-001',
                'is_featured' => true,
                'category_name' => 'Sport & Fitness',
            ],
            [
                'name' => 'Set Gantere Reglabile',
                'description' => 'Set gantere reglabile 2x20kg, mânere ergonomice, discuri din fontă, bara din oțel inoxidabil.',
                'short_description' => 'Set gantere reglabile 2x20kg premium',
                'price' => 499.99,
                'original_price' => 649.99,
                'stock_quantity' => 20,
                'sku' => 'SPORT-DUMBBELL-001',
                'is_featured' => false,
                'category_name' => 'Sport & Fitness',
            ],
            [
                'name' => 'Adidași Sport Nike',
                'description' => 'Adidași sport pentru alergare, talpă amortizare, material respirabil, mărimi 38-46.',
                'short_description' => 'Adidași sport cu talpă amortizare',
                'price' => 449.99,
                'original_price' => 599.99,
                'stock_quantity' => 40,
                'sku' => 'SPORT-SHOES-NIKE-001',
                'is_featured' => false,
                'category_name' => 'Sport & Fitness',
            ],

            // Jucării
            [
                'name' => 'Set Lego Creator',
                'description' => 'Set Lego Creator cu 1500+ piese, pentru copii 8+ ani, instrucțiuni detaliate, multiple modele de construit.',
                'short_description' => 'Set Lego Creator cu 1500+ piese',
                'price' => 299.99,
                'original_price' => 399.99,
                'stock_quantity' => 30,
                'sku' => 'TOYS-LEGO-001',
                'is_featured' => false,
                'category_name' => 'Jucării',
            ],
            [
                'name' => 'Păpușă Barbie Premium',
                'description' => 'Păpușă Barbie premium cu accesorii, haine detașabile, păr pe care poate fi coafat, pentru copii 3+ ani.',
                'short_description' => 'Păpușă Barbie premium cu accesorii',
                'price' => 149.99,
                'original_price' => 199.99,
                'stock_quantity' => 25,
                'sku' => 'TOYS-BARBIE-001',
                'is_featured' => false,
                'category_name' => 'Jucării',
            ],

            // Frumusețe
            [
                'name' => 'Set Cosmetice Premium',
                'description' => 'Set complet cosmetice premium, cremă hidratantă, ser, demachiant, pentru toate tipurile de piele.',
                'short_description' => 'Set complet cosmetice premium',
                'price' => 399.99,
                'original_price' => 549.99,
                'stock_quantity' => 20,
                'sku' => 'BEAUTY-SET-001',
                'is_featured' => false,
                'category_name' => 'Frumusețe',
            ],
            [
                'name' => 'Parfum Dama 100ml',
                'description' => 'Parfum de lux pentru femei, 100ml, notă florală, persistare 8-10 ore, cutie originală.',
                'short_description' => 'Parfum de lux pentru femei, 100ml',
                'price' => 299.99,
                'original_price' => 399.99,
                'stock_quantity' => 15,
                'sku' => 'BEAUTY-PERFUME-001',
                'is_featured' => false,
                'category_name' => 'Frumusețe',
            ],

            // Auto
            [
                'name' => 'Set Cauciucuri Iarnă',
                'description' => 'Set 4 cauciucuri iarnă, mărime 205/55 R16, index viteză H, pentru toate condițiile meteo.',
                'short_description' => 'Set 4 cauciucuri iarnă premium',
                'price' => 1299.99,
                'original_price' => 1699.99,
                'stock_quantity' => 12,
                'sku' => 'AUTO-TIRES-001',
                'is_featured' => false,
                'category_name' => 'Auto',
            ],
            [
                'name' => 'Navigator GPS Garmin',
                'description' => 'Navigator GPS Garmin cu ecran 7", actualizări gratuite hărți, alertă trafic, Bluetooth.',
                'short_description' => 'Navigator GPS cu ecran 7" și actualizări gratuite',
                'price' => 799.99,
                'original_price' => 999.99,
                'stock_quantity' => 10,
                'sku' => 'AUTO-GPS-001',
                'is_featured' => false,
                'category_name' => 'Auto',
            ],

            // Cărți
            [
                'name' => 'Carte "Arta Programării"',
                'description' => 'Carte despre programare și algoritmi, ediție actualizată, 800+ pagini, exemple practice, pentru toate nivelurile.',
                'short_description' => 'Carte despre programare și algoritmi',
                'price' => 149.99,
                'original_price' => 199.99,
                'stock_quantity' => 30,
                'sku' => 'BOOK-PROG-001',
                'is_featured' => false,
                'category_name' => 'Cărți',
            ],
            [
                'name' => 'Roman "Lumea Nouă"',
                'description' => 'Roman bestseller, ediție specială, copertă cartonată, 450 pagini, autor recunoscut internațional.',
                'short_description' => 'Roman bestseller, ediție specială',
                'price' => 79.99,
                'original_price' => 99.99,
                'stock_quantity' => 50,
                'sku' => 'BOOK-NOVEL-001',
                'is_featured' => false,
                'category_name' => 'Cărți',
            ],
        ];

        foreach ($products as $productData) {
            $category = $categories->firstWhere('name', $productData['category_name']);

            if (!$category) {
                continue;
            }

            $discount = null;
            if ($productData['original_price'] && $productData['original_price'] > $productData['price']) {
                $discount = (int) round((($productData['original_price'] - $productData['price']) / $productData['original_price']) * 100);
            }

            Product::create([
                'name' => $productData['name'],
                'slug' => Str::slug($productData['name']),
                'description' => $productData['description'],
                'short_description' => $productData['short_description'],
                'price' => $productData['price'],
                'original_price' => $productData['original_price'] ?? null,
                'discount_percentage' => $discount,
                'sku' => $productData['sku'],
                'category_id' => $category->id,
                'stock_quantity' => $productData['stock_quantity'],
                'in_stock' => $productData['stock_quantity'] > 0,
                'is_active' => true,
                'is_featured' => $productData['is_featured'] ?? false,
                'sort_order' => 0,
                'image' => 'https://ilise.md/wp-content/uploads/2022/07/Preview_2.1_Golden-Park_BrandWall-2_2900x2500mm-720x540.webp',
            ]);
        }

        $this->command->info('Produse create cu succes!');
    }
}
