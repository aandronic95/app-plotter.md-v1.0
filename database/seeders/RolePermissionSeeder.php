<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Rulează seed-urile pentru roluri și permisiuni.
     */
    public function run(): void
    {
        // Resetează cache-ul pentru roluri și permisiuni
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Definește toate permisiunile grupate pe resurse
        $permissions = [
            // Permisiuni pentru utilizatori
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Permisiuni pentru roluri
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            // Permisiuni pentru permisiuni
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',

            // Permisiuni pentru produse
            'view products',
            'create products',
            'edit products',
            'delete products',

            // Permisiuni pentru categorii
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',

            // Permisiuni pentru comenzi
            'view orders',
            'create orders',
            'edit orders',
            'delete orders',
            'manage orders', // Pentru actualizări de status, etc.

            // Permisiuni pentru pagini
            'view pages',
            'create pages',
            'edit pages',
            'delete pages',

            // Permisiuni pentru navigare
            'view navigation',
            'create navigation',
            'edit navigation',
            'delete navigation',

            // Permisiuni pentru setări site
            'view site settings',
            'create site settings',
            'edit site settings',
            'delete site settings',

            // Permisiuni pentru promoții
            'view promotions',
            'create promotions',
            'edit promotions',
            'delete promotions',

            // Acces la panoul de administrare
            'access admin panel',
        ];

        // Creează permisiunile
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        // Creează rolurile
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editorRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Atribuie toate permisiunile rolului admin
        $adminRole->syncPermissions(Permission::all());

        // Atribuie permisiuni rolului editor (gestionare conținut)
        $editorRole->syncPermissions([
            'view products',
            'create products',
            'edit products',
            'delete products',
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'view pages',
            'create pages',
            'edit pages',
            'delete pages',
            'view navigation',
            'create navigation',
            'edit navigation',
            'delete navigation',
            'view promotions',
            'create promotions',
            'edit promotions',
            'delete promotions',
            'view orders',
            'access admin panel',
        ]);

        // Atribuie permisiuni rolului manager (comenzi și conținut)
        $managerRole->syncPermissions([
            'view products',
            'edit products',
            'view categories',
            'edit categories',
            'view orders',
            'edit orders',
            'manage orders',
            'view pages',
            'edit pages',
            'view navigation',
            'edit navigation',
            'view promotions',
            'edit promotions',
            'view users',
            'access admin panel',
        ]);

        // Rolul user nu are permisiuni de administrare (utilizator obișnuit)
        $userRole->syncPermissions([]);

        $this->command->info('Ruoli și permisiuni create cu succes!');
        $this->command->info('Ruoli: admin, editor, manager, user');
        $this->command->info('Total permisiuni: ' . Permission::count());
    }
}
