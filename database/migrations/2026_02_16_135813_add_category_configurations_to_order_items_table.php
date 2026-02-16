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
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'format')) {
                $table->string('format', 255)->nullable()->after('configuration_quantity');
            }
            if (!Schema::hasColumn('order_items', 'suport')) {
                $table->string('suport', 100)->nullable()->after('format');
            }
            if (!Schema::hasColumn('order_items', 'culoare')) {
                $table->string('culoare', 100)->nullable()->after('suport');
            }
            if (!Schema::hasColumn('order_items', 'colturi')) {
                $table->string('colturi', 100)->nullable()->after('culoare');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['format', 'suport', 'culoare', 'colturi']);
        });
    }
};
