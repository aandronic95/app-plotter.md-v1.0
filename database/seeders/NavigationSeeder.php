<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Navigation;
use Illuminate\Database\Seeder;

class NavigationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $navigations = [
            // Main sidebar navigation
            [
                'title' => 'Dashboard',
                'href' => '/dashboard',
                'icon' => 'LayoutGrid',
                'group' => 'main',
                'sort_order' => 1,
                'is_active' => true,
                'is_external' => false,
                'target' => '_self',
            ],
            // Header navigation
            [
                'title' => 'Acasă',
                'href' => '/',
                'icon' => null,
                'group' => 'header',
                'sort_order' => 1,
                'is_active' => true,
                'is_external' => false,
                'target' => '_self',
            ],
            [
                'title' => 'Produse',
                'href' => '/products',
                'icon' => null,
                'group' => 'header',
                'sort_order' => 2,
                'is_active' => true,
                'is_external' => false,
                'target' => '_self',
            ],
            [
                'title' => 'Categorii',
                'href' => '/categories',
                'icon' => null,
                'group' => 'header',
                'sort_order' => 3,
                'is_active' => true,
                'is_external' => false,
                'target' => '_self',
            ],
            [
                'title' => 'Despre noi',
                'href' => '/about',
                'icon' => null,
                'group' => 'header',
                'sort_order' => 4,
                'is_active' => true,
                'is_external' => false,
                'target' => '_self',
            ],
            [
                'title' => 'Contact',
                'href' => '/contact',
                'icon' => null,
                'group' => 'header',
                'sort_order' => 5,
                'is_active' => true,
                'is_external' => false,
                'target' => '_self',
            ],
            // Footer navigation - Company
            [
                'title' => 'Despre noi',
                'href' => '/about',
                'icon' => null,
                'group' => 'footer',
                'category' => 'company',
                'sort_order' => 1,
                'is_active' => true,
                'is_external' => false,
                'target' => '_self',
            ],
            [
                'title' => 'Contact',
                'href' => '/contact',
                'icon' => null,
                'group' => 'footer',
                'category' => 'company',
                'sort_order' => 2,
                'is_active' => true,
                'is_external' => false,
                'target' => '_self',
            ],
            [
                'title' => 'Cariere',
                'href' => '/careers',
                'icon' => null,
                'group' => 'footer',
                'category' => 'company',
                'sort_order' => 3,
                'is_active' => true,
                'is_external' => false,
                'target' => '_self',
            ],
            [
                'title' => 'Blog',
                'href' => '/blog',
                'icon' => null,
                'group' => 'footer',
                'category' => 'company',
                'sort_order' => 4,
                'is_active' => true,
                'is_external' => false,
                'target' => '_self',
            ],
            // Footer navigation - Customer Service
            [
                'title' => 'Centru de ajutor',
                'href' => '/help',
                'icon' => null,
                'group' => 'footer',
                'category' => 'customer',
                'sort_order' => 5,
                'is_active' => true,
                'is_external' => false,
                'target' => '_self',
            ],
            [
                'title' => 'Livrare',
                'href' => '/shipping',
                'icon' => null,
                'group' => 'footer',
                'category' => 'customer',
                'sort_order' => 6,
                'is_active' => true,
                'is_external' => false,
                'target' => '_self',
            ],
            [
                'title' => 'Returnări',
                'href' => '/returns',
                'icon' => null,
                'group' => 'footer',
                'category' => 'customer',
                'sort_order' => 7,
                'is_active' => true,
                'is_external' => false,
                'target' => '_self',
            ],
            [
                'title' => 'FAQ',
                'href' => '/faq',
                'icon' => null,
                'group' => 'footer',
                'category' => 'customer',
                'sort_order' => 8,
                'is_active' => true,
                'is_external' => false,
                'target' => '_self',
            ],
            // Footer navigation - Legal
            [
                'title' => 'Termeni și condiții',
                'href' => '/terms',
                'icon' => null,
                'group' => 'footer',
                'category' => 'legal',
                'sort_order' => 9,
                'is_active' => true,
                'is_external' => false,
                'target' => '_self',
            ],
            [
                'title' => 'Politica de confidențialitate',
                'href' => '/privacy',
                'icon' => null,
                'group' => 'footer',
                'category' => 'legal',
                'sort_order' => 10,
                'is_active' => true,
                'is_external' => false,
                'target' => '_self',
            ],
            [
                'title' => 'Cookie-uri',
                'href' => '/cookies',
                'icon' => null,
                'group' => 'footer',
                'category' => 'legal',
                'sort_order' => 11,
                'is_active' => true,
                'is_external' => false,
                'target' => '_self',
            ],
            [
                'title' => 'GDPR',
                'href' => '/gdpr',
                'icon' => null,
                'group' => 'footer',
                'category' => 'legal',
                'sort_order' => 12,
                'is_active' => true,
                'is_external' => false,
                'target' => '_self',
            ],
        ];

        foreach ($navigations as $navigation) {
            Navigation::updateOrCreate(
                ['href' => $navigation['href'], 'group' => $navigation['group']],
                $navigation
            );
        }
    }
}

