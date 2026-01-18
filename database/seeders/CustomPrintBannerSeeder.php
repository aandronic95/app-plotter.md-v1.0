<?php

namespace Database\Seeders;

use App\Models\CustomPrintBanner;
use Illuminate\Database\Seeder;

class CustomPrintBannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CustomPrintBanner::create([
            'headline' => 'Hai să tipărim propriul tău produs !',
            'title' => null,
            'description' => 'Aici, poți personaliza cu ușurință propriul tău produs și apoi să-l tipărești exact aşa cum îți dorești. Fie că este vorba despre invitații, afişe sau materiale promoţionale.',
            'button_text' => 'Tipăreşte macheta mea',
            'button_link' => '/products',
            'image' => null,
            'background_color' => '#fafafa', // Neutral light gray to match other components
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }
}
