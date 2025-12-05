<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CheckProductImages extends Command
{
    protected $signature = 'products:check-images {slug?}';
    protected $description = 'Verifică starea imaginilor pentru produse';

    public function handle(): int
    {
        $slug = $this->argument('slug');
        
        if ($slug) {
            $products = Product::where('slug', $slug)->get();
        } else {
            $products = Product::whereNotNull('image')->orWhereNotNull('images')->get();
        }

        if ($products->isEmpty()) {
            $this->warn('Nu există produse de verificat.');
            return Command::SUCCESS;
        }

        foreach ($products as $product) {
            $this->line("=== {$product->name} (slug: {$product->slug}) ===");
            
            // Verifică imaginea principală
            if ($product->image) {
                $this->line("Imagine principală: {$product->image}");
                if (str_starts_with($product->image, 'http')) {
                    $this->info("  → URL extern");
                } else {
                    $exists = Storage::disk('public')->exists($product->image);
                    $this->{$exists ? 'info' : 'error'}("  → " . ($exists ? 'EXISTĂ' : 'NU EXISTĂ'));
                    
                    if ($exists) {
                        $url = Storage::disk('public')->url($product->image);
                        $this->line("  → URL: {$url}");
                    }
                }
            } else {
                $this->warn("  → Fără imagine principală");
            }

            // Verifică imaginile suplimentare
            if ($product->images && is_array($product->images)) {
                $this->line("Imagini suplimentare: " . count($product->images));
                foreach ($product->images as $index => $image) {
                    if (str_starts_with($image, 'http')) {
                        $this->info("  [{$index}] URL extern: {$image}");
                    } else {
                        $exists = Storage::disk('public')->exists($image);
                        $this->{$exists ? 'info' : 'error'}("  [{$index}] " . ($exists ? 'EXISTĂ' : 'NU EXISTĂ') . ": {$image}");
                        
                        if ($exists) {
                            $url = Storage::disk('public')->url($image);
                            $this->line("      URL: {$url}");
                        }
                    }
                }
            } else {
                $this->warn("  → Fără imagini suplimentare");
            }

            $this->newLine();
        }

        return Command::SUCCESS;
    }
}

