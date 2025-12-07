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
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('loading_text_main')->nullable()->after('show_loyalty_points')->default('tanavius');
            $table->string('loading_text_sub')->nullable()->after('loading_text_main')->default('www.plotter.md');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['loading_text_main', 'loading_text_sub']);
        });
    }
};
