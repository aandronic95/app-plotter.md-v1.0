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
            // Check if columns exist before adding them
            if (!Schema::hasColumn('product_configurations', 'suport')) {
                $table->string('suport', 100)->nullable()->after('format');
            }
            if (!Schema::hasColumn('product_configurations', 'culoare')) {
                $table->string('culoare', 100)->nullable()->after('suport');
            }
            if (!Schema::hasColumn('product_configurations', 'colturi')) {
                $table->string('colturi', 100)->nullable()->after('culoare');
            }
            
            // Add individual indexes for better query performance
            $table->index('suport');
            $table->index('culoare');
            $table->index('colturi');
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
            
            // Restore the old unique constraint
            $table->unique(['product_id', 'print_size', 'print_sides', 'format', 'quantity'], 'unique_product_config');
            
            $table->dropColumn(['suport', 'culoare', 'colturi']);
        });
    }
};
