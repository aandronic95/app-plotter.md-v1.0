<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "🔧 Fixare acces admin...\n\n";

// Rulează seeder-ul
echo "📦 Creare roluri și permisiuni...\n";
$seeder = new \Database\Seeders\RolePermissionSeeder();
$seeder->run();
echo "✓ Roluri și permisiuni create\n\n";

// Verifică utilizatorii
$users = User::all();

if ($users->isEmpty()) {
    echo "⚠ Nu există utilizatori în baza de date.\n";
    echo "💡 Creează un utilizator cu: php artisan make:filament-user\n";
    exit(1);
}

echo "👥 Găsiți {$users->count()} utilizator(i)\n\n";

// Atribuie rolul admin
$adminRole = Role::where('name', 'admin')->where('guard_name', 'web')->first();

if (!$adminRole) {
    echo "❌ Eroare: Rolul admin nu există!\n";
    exit(1);
}

$assigned = 0;
foreach ($users as $user) {
    $currentRoles = $user->getRoleNames();
    
    echo "📧 {$user->email} ({$user->name})\n";
    echo "   Roluri actuale: " . ($currentRoles->isEmpty() ? 'Niciunul' : $currentRoles->implode(', ')) . "\n";
    
    if (!$user->hasRole('admin')) {
        $user->assignRole('admin');
        $user->refresh();
        echo "   ✓ Rolul admin a fost atribuit\n";
        $assigned++;
    } else {
        echo "   ℹ Are deja rolul admin\n";
    }
    echo "\n";
}

// Curăță cache-ul
echo "🧹 Curățare cache...\n";
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
\Illuminate\Support\Facades\Artisan::call('cache:clear');
\Illuminate\Support\Facades\Artisan::call('config:clear');

echo "\n✅ Gata! {$assigned} utilizator(i) au primit rolul admin.\n";
echo "💡 Acum poți accesa panoul admin la: /admin\n";
