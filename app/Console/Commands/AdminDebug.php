<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class AdminDebug extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:debug 
                            {--delete-user-1 : Șterge utilizatorul cu ID 1}
                            {--create-admin : Creează un admin nou}
                            {--email= : Email pentru noul admin}
                            {--name= : Nume pentru noul admin}
                            {--password= : Parolă pentru noul admin (default: password)}
                            {--list : Listează toți adminii}
                            {--check : Verifică un utilizator specific}
                            {--check-email= : Email pentru verificare}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Script de debug pentru admin: șterge user 1, creează admin nou, listează adminii';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // List all admins
        if ($this->option('list')) {
            return $this->listAdmins();
        }

        // Check specific user
        if ($this->option('check')) {
            return $this->checkUser();
        }

        // Delete user with ID 1
        if ($this->option('delete-user-1')) {
            return $this->deleteUser1();
        }

        // Create new admin
        if ($this->option('create-admin')) {
            return $this->createAdmin();
        }

        // If no option specified, show help
        $this->info('Utilizare:');
        $this->line('  php artisan admin:debug --list                    # Listează toți adminii');
        $this->line('  php artisan admin:debug --check --check-email=...  # Verifică un utilizator');
        $this->line('  php artisan admin:debug --delete-user-1            # Șterge utilizatorul cu ID 1');
        $this->line('  php artisan admin:debug --create-admin --email=... --name=... [--password=...]');
        $this->line('');
        $this->line('Exemple:');
        $this->line('  php artisan admin:debug --create-admin --email=admin@example.com --name="Admin User"');
        $this->line('  php artisan admin:debug --create-admin --email=admin@example.com --name="Admin" --password=secret123');

        return Command::SUCCESS;
    }

    /**
     * List all admin users
     */
    private function listAdmins(): int
    {
        $this->info('=== Lista Adminilor ===');
        $this->line('');

        $admins = User::all()->filter(fn ($user) => $user->isAdmin());

        if ($admins->isEmpty()) {
            $this->warn('Nu există admini în baza de date!');
            return Command::FAILURE;
        }

        $tableData = [];
        foreach ($admins as $admin) {
            $tableData[] = [
                'ID' => $admin->id,
                'Nume' => $admin->name,
                'Email' => $admin->email,
                'Role' => $admin->role,
                'Is Admin' => $admin->isAdmin() ? 'DA' : 'NU',
                'Creat la' => $admin->created_at->format('Y-m-d H:i:s'),
            ];
        }

        $this->table(
            ['ID', 'Nume', 'Email', 'Role', 'Is Admin', 'Creat la'],
            $tableData
        );

        return Command::SUCCESS;
    }

    /**
     * Check specific user
     */
    private function checkUser(): int
    {
        $email = $this->option('check-email');

        if (!$email) {
            $this->error('Trebuie să specifici --check-email=...');
            return Command::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Utilizatorul cu email-ul {$email} nu a fost găsit.");
            return Command::FAILURE;
        }

        $this->info("=== Informații Utilizator ===");
        $this->line("ID: {$user->id}");
        $this->line("Nume: {$user->name}");
        $this->line("Email: {$user->email}");
        $this->line("Role: {$user->role} (type: " . gettype($user->role) . ")");
        $this->line("Is Admin: " . ($user->isAdmin() ? 'DA' : 'NU'));
        
        // Test canAccessPanel
        try {
            $panel = \Filament\Facades\Filament::getPanel('admin');
            $canAccess = $user->canAccessPanel($panel);
            $this->line("Can Access Panel: " . ($canAccess ? 'DA' : 'NU'));
        } catch (\Exception $e) {
            $this->warn("Nu s-a putut verifica canAccessPanel: " . $e->getMessage());
        }

        $this->line("Creat la: {$user->created_at->format('Y-m-d H:i:s')}");
        $this->line("Actualizat la: {$user->updated_at->format('Y-m-d H:i:s')}");

        return Command::SUCCESS;
    }

    /**
     * Delete user with ID 1
     */
    private function deleteUser1(): int
    {
        $user = User::find(1);

        if (!$user) {
            $this->warn('Utilizatorul cu ID 1 nu există.');
            return Command::SUCCESS;
        }

        $this->info("=== Ștergere Utilizator ID 1 ===");
        $this->line("ID: {$user->id}");
        $this->line("Nume: {$user->name}");
        $this->line("Email: {$user->email}");
        $this->line("Role: {$user->role}");

        if (!$this->confirm('Ești sigur că vrei să ștergi acest utilizator?', false)) {
            $this->info('Operațiune anulată.');
            return Command::SUCCESS;
        }

        try {
            $user->delete();
            $this->info('✓ Utilizatorul cu ID 1 a fost șters cu succes!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Eroare la ștergere: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Create new admin user
     */
    private function createAdmin(): int
    {
        $email = $this->option('email');
        $name = $this->option('name');
        $password = $this->option('password') ?? 'password';

        if (!$email || !$name) {
            $this->error('Trebuie să specifici --email=... și --name=...');
            $this->line('');
            $this->line('Exemplu:');
            $this->line('  php artisan admin:debug --create-admin --email=admin@example.com --name="Admin User"');
            return Command::FAILURE;
        }

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            $this->error("Un utilizator cu email-ul {$email} există deja!");
            if ($this->confirm('Vrei să actualizezi rolul la admin?', false)) {
                $user = User::where('email', $email)->first();
                $user->role = 'admin';
                $user->save();
                $this->info("✓ Rolul utilizatorului {$email} a fost actualizat la 'admin'!");
                return Command::SUCCESS;
            }
            return Command::FAILURE;
        }

        // Validate password
        if (strlen($password) < 8) {
            $this->warn('Parola este prea scurtă (minimum 8 caractere).');
            if (!$this->confirm('Vrei să continui cu parola actuală?', false)) {
                return Command::FAILURE;
            }
        }

        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            $this->info('=== Admin Creat cu Succes ===');
            $this->line("ID: {$user->id}");
            $this->line("Nume: {$user->name}");
            $this->line("Email: {$user->email}");
            $this->line("Role: {$user->role}");
            $this->line("Is Admin: " . ($user->isAdmin() ? 'DA' : 'NU'));
            $this->line("Parolă: {$password}");
            $this->line('');
            $this->warn('⚠️  NU UITA să schimbi parola după prima autentificare!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Eroare la creare: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
