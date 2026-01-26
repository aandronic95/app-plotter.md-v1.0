<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('print_size'); // A3, A4
            $table->string('print_sides'); // 4+0 (1-sided), 4+4 (2-sided)
            $table->integer('quantity'); // 500, 1000, 2000
            $table->decimal('price', 10, 2); // Total price
            $table->decimal('price_per_unit', 10, 2); // Price per piece
            $table->integer('production_days')->default(5); // Days to produce
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'is_active']);
            $table->index(['print_size', 'print_sides', 'quantity']);
            $table->unique(['product_id', 'print_size', 'print_sides', 'quantity'], 'unique_product_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_configurations');
    }
};
