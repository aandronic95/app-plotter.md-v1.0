<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\HeroBanner;
use Illuminate\Database\Seeder;

class HeroBannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HeroBanner::create([
            'headline' => 'APELEAZĂ LA NOI',
            'title' => 'PRINTĂM HAINE',
            'description' => 'Cu tehnologia sa avansată, imprimarea pe materiale rigide reprezintă o soluție inovatoare și durabilă pentru proiectele tale.',
            'features' => ['RAPID', 'CALITATIV'],
            'button1_text' => 'SPRE MAGAZIN',
            'button1_link' => '/products',
            'button2_text' => 'IMPRIMĂ PROPRIUL DESIGN',
            'button2_link' => '/custom-design',
            'image' => null, // Se va încărca manual imaginea
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }
}
