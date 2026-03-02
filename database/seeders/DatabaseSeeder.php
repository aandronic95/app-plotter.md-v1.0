<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        // Creează sau actualizează utilizatorul admin (idempotent)
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin'),
            ]
        );
        $admin->assignRole('admin');

        // Creează sau actualizează utilizator obișnuit (idempotent)
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
            ]
        );
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
