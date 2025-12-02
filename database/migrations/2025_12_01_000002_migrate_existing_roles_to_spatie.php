<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if roles table exists
        if (!Schema::hasTable('roles')) {
            throw new \Exception('Tabelul roles nu există. Rulează mai întâi migrația create_permission_tables.');
        }

        // Create default roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Create default permissions
        $permissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
            'access admin panel',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign all permissions to admin role
        $adminRole->syncPermissions(Permission::all());

        // Migrate existing users' roles only if role column exists
        if (Schema::hasColumn('users', 'role')) {
            $users = DB::table('users')->get();
            
            foreach ($users as $user) {
                try {
                    $userModel = \App\Models\User::find($user->id);
                    
                    if (!$userModel) {
                        continue;
                    }

                    // Remove old role assignment if exists
                    $userModel->roles()->detach();

                    // Assign role based on existing role field
                    if ($user->role === 'admin' || $user->role === 1 || $user->role === '1') {
                        $userModel->assignRole('admin');
                    } else {
                        $userModel->assignRole('user');
                    }
                } catch (\Exception $e) {
                    // Log error but continue with other users
                    \Log::warning("Eroare la migrarea rolului pentru utilizatorul ID {$user->id}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not easily reversible
        // You would need to restore the old role column values
    }
};
