<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:set-role {email} {role : Rolul de setat (admin sau user)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setează rolul unui utilizator (admin sau user)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $role = $this->argument('role');

        if (!in_array($role, ['admin', 'user'])) {
            $this->error('Rolul trebuie să fie "admin" sau "user".');
            return Command::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Utilizatorul cu email-ul {$email} nu a fost găsit.");
            return Command::FAILURE;
        }

        // Ensure the role exists
        try {
            $roleModel = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $role, 'guard_name' => 'web']
            );
        } catch (\Exception $e) {
            $this->error("Eroare la crearea rolului: " . $e->getMessage());
            $this->warn("Asigură-te că ai rulat migrațiile: php artisan migrate");
            return Command::FAILURE;
        }

        // Remove all existing roles and assign the new one
        $user->syncRoles([$role]);

        $roleLabel = $role === 'admin' ? 'Administrator' : 'Utilizator';
        $this->info("✓ Rolul utilizatorului {$user->name} ({$email}) a fost setat la: {$roleLabel}");
        
        // Verify
        $user->refresh();
        if ($user->hasRole($role)) {
            $this->info("✓ Verificare: Utilizatorul are acum rolul '{$role}'");
        } else {
            $this->warn("⚠ Avertisment: Rolul nu pare să fie atribuit corect.");
        }

        return Command::SUCCESS;
    }
}
