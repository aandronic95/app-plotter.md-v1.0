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
        Schema::table('product_configurations', function (Blueprint $table) {
            // Drop the old unique constraint
            $table->dropUnique('unique_product_config');
            
            // Add new unique constraint that includes format
            $table->unique(['product_id', 'print_size', 'print_sides', 'format', 'quantity'], 'unique_product_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_configurations', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('unique_product_config');
            
            // Restore the old unique constraint without format
            $table->unique(['product_id', 'print_size', 'print_sides', 'quantity'], 'unique_product_config');
        });
    }
};
