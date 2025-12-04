<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class FixAdminAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:fix-access {--email= : Email-ul utilizatorului specific}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asigură că rolurile și permisiunile sunt create și atribuie rolul admin';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Verificare și fixare acces admin...');
        $this->newLine();

        // Rulează seeder-ul pentru roluri și permisiuni
        $this->info('📦 Creare roluri și permisiuni...');
        $this->call('db:seed', ['--class' => 'RolePermissionSeeder']);
        $this->newLine();

        // Verifică dacă există rolul admin
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'web')->first();
        
        if (!$adminRole) {
            $this->error('❌ Rolul admin nu există! Rulează mai întâi: php artisan db:seed --class=RolePermissionSeeder');
            return Command::FAILURE;
        }

        $this->info('✓ Rolul admin există');
        $this->newLine();

        // Găsește utilizatorii
        $email = $this->option('email');
        
        if ($email) {
            $users = User::where('email', $email)->get();
            if ($users->isEmpty()) {
                $this->error("❌ Utilizatorul cu email-ul {$email} nu a fost găsit.");
                return Command::FAILURE;
            }
        } else {
            $users = User::all();
            if ($users->isEmpty()) {
                $this->warn('⚠ Nu există utilizatori în baza de date.');
                $this->info('💡 Creează un utilizator cu: php artisan make:filament-user');
                return Command::SUCCESS;
            }
        }

        $this->info('👥 Utilizatori găsiți: ' . $users->count());
        $this->newLine();

        // Atribuie rolul admin
        $assigned = 0;
        foreach ($users as $user) {
            $currentRoles = $user->getRoleNames();
            
            $this->line("📧 {$user->email} ({$user->name})");
            $this->line("   Roluri actuale: " . ($currentRoles->isEmpty() ? 'Niciunul' : $currentRoles->implode(', ')));
            
            if (!$user->hasRole('admin')) {
                $user->assignRole('admin');
                $user->refresh();
                $this->info("   ✓ Rolul admin a fost atribuit");
                $assigned++;
            } else {
                $this->comment("   ℹ Are deja rolul admin");
            }
            $this->newLine();
        }

        // Curăță cache-ul
        $this->info('🧹 Curățare cache...');
        $this->call('permission:cache-reset');
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->newLine();

        $this->info("✅ Gata! {$assigned} utilizator(i) au primit rolul admin.");
        $this->newLine();
        $this->comment('💡 Acum poți accesa panoul admin la: /admin');

        return Command::SUCCESS;
    }
}
