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
            $table->string('print_size')->nullable()->after('product_sku');
            $table->string('print_sides')->nullable()->after('print_size');
            $table->integer('configuration_quantity')->nullable()->after('print_sides');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['print_size', 'print_sides', 'configuration_quantity']);
        });
    }
};
