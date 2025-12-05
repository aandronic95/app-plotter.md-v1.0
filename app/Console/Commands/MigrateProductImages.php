<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:migrate-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mută imaginile produselor din products/ în images/products/';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔄 Încep migrarea imaginilor produselor...');
        $this->newLine();

        $products = Product::whereNotNull('image')
            ->orWhereNotNull('images')
            ->get();

        if ($products->isEmpty()) {
            $this->warn('Nu există produse cu imagini de migrat.');
            return Command::SUCCESS;
        }

        $moved = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($products as $product) {
            $this->line("Procesez produs: {$product->name} (slug: {$product->slug})");

            $needsUpdate = false;
            $updatedImage = $product->image;
            $updatedImages = $product->images ?? [];

            // Mută imaginea principală
            if ($product->image && !str_starts_with($product->image, 'http')) {
                $oldPath = $product->image;
                
                // Verifică dacă imaginea este în vechea locație (products/)
                if (str_contains($oldPath, 'products/') && !str_contains($oldPath, 'images/products/')) {
                    $newPath = str_replace('products/', 'images/products/', $oldPath);
                    
                    // Creează directorul dacă nu există
                    $directory = dirname($newPath);
                    Storage::disk('public')->makeDirectory($directory);
                    
                    // Mută fișierul dacă există
                    if (Storage::disk('public')->exists($oldPath)) {
                        if (Storage::disk('public')->move($oldPath, $newPath)) {
                            $updatedImage = $newPath;
                            $needsUpdate = true;
                            $moved++;
                            $this->info("  ✓ Imagine principală mutată: {$oldPath} -> {$newPath}");
                        } else {
                            $errors++;
                            $this->error("  ✗ Eroare la mutarea imaginii principale: {$oldPath}");
                        }
                    } else {
                        $skipped++;
                        $this->warn("  ⚠ Imaginea principală nu există: {$oldPath}");
                    }
                } else {
                    $skipped++;
                    $this->line("  - Imagine principală deja în locația corectă sau URL extern");
                }
            }

            // Mută imaginile suplimentare
            if ($product->images && is_array($product->images)) {
                $newImages = [];
                
                foreach ($product->images as $imagePath) {
                    if (str_starts_with($imagePath, 'http')) {
                        // URL extern, păstrează-l
                        $newImages[] = $imagePath;
                        continue;
                    }
                    
                    // Verifică dacă imaginea este în vechea locație (products/)
                    if (str_contains($imagePath, 'products/') && !str_contains($imagePath, 'images/products/')) {
                        $newPath = str_replace('products/', 'images/products/', $imagePath);
                        
                        // Creează directorul dacă nu există
                        $directory = dirname($newPath);
                        Storage::disk('public')->makeDirectory($directory);
                        
                        // Mută fișierul dacă există
                        if (Storage::disk('public')->exists($imagePath)) {
                            if (Storage::disk('public')->move($imagePath, $newPath)) {
                                $newImages[] = $newPath;
                                $needsUpdate = true;
                                $moved++;
                                $this->info("  ✓ Imagine suplimentară mutată: {$imagePath} -> {$newPath}");
                            } else {
                                $newImages[] = $imagePath;
                                $errors++;
                                $this->error("  ✗ Eroare la mutarea imaginii suplimentare: {$imagePath}");
                            }
                        } else {
                            $newImages[] = $imagePath;
                            $skipped++;
                            $this->warn("  ⚠ Imaginea suplimentară nu există: {$imagePath}");
                        }
                    } else {
                        $newImages[] = $imagePath;
                        $skipped++;
                        $this->line("  - Imagine suplimentară deja în locația corectă sau URL extern");
                    }
                }
                
                $updatedImages = $newImages;
            }

            // Actualizează produsul dacă s-au făcut modificări
            if ($needsUpdate) {
                $product->withoutEvents(function () use ($product, $updatedImage, $updatedImages) {
                    $product->update([
                        'image' => $updatedImage,
                        'images' => $updatedImages,
                    ]);
                });
            }
        }

        $this->newLine();
        $this->info("✅ Migrare finalizată!");
        $this->line("📊 Statistici:");
        $this->line("   - Imagini mutate: {$moved}");
        $this->line("   - Imagini omise: {$skipped}");
        $this->line("   - Erori: {$errors}");

        return Command::SUCCESS;
    }
}

