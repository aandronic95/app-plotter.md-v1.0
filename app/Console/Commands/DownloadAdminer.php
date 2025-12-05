<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DownloadAdminer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'adminer:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Descarcă Adminer de la sursa oficială';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $adminerPath = public_path('adminer.php');

        if (File::exists($adminerPath)) {
            if (!$this->confirm('Fișierul adminer.php există deja. Vrei să-l suprascrii?', false)) {
                $this->info('Operațiune anulată.');
                return Command::SUCCESS;
            }
        }

        $this->info('📥 Descărcare Adminer...');

        // URL-ul oficial pentru Adminer
        $adminerUrl = 'https://www.adminer.org/latest.php';

        $this->line("URL: {$adminerUrl}");

        $content = @file_get_contents($adminerUrl);

        if ($content === false) {
            $this->error('❌ Nu s-a putut descărca Adminer de la sursa oficială.');
            $this->newLine();
            $this->warn('💡 Instalare manuală:');
            $this->line('1. Accesează: https://www.adminer.org/');
            $this->line('2. Descarcă fișierul PHP');
            $this->line('3. Salvează-l ca: ' . $adminerPath);
            return Command::FAILURE;
        }

        File::put($adminerPath, $content);

        $fileSize = File::size($adminerPath);
        $this->info("✅ Adminer descărcat cu succes!");
        $this->line("📁 Locație: {$adminerPath}");
        $this->line("📊 Dimensiune: " . number_format($fileSize / 1024, 2) . " KB");
        $this->newLine();
        $this->comment('💡 Accesează Adminer la: /adminer (doar pentru admini)');

        return Command::SUCCESS;
    }
}
