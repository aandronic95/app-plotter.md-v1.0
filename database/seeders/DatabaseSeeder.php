<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Rulează mai întâi seeder-ul pentru roluri și permisiuni
        $this->call([
            RolePermissionSeeder::class,
        ]);

        // Creează utilizatorul admin
        $admin = User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('admin');

        // Creează utilizator obișnuit
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $user->assignRole('user');

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            NavigationSeeder::class,
            HeroBannerSeeder::class,
            ProductCategoryShowcaseSeeder::class,
            DeliveryMethodSeeder::class,
        ]);
    }
}
