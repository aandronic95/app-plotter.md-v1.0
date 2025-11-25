<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('Laravel');
            $table->text('site_description')->nullable();
            $table->string('site_logo')->nullable()->comment('Path to logo image');
            $table->string('site_logo_icon')->nullable()->comment('Path to logo icon (SVG)');
            $table->string('site_favicon')->nullable();
            $table->string('site_email')->nullable();
            $table->string('site_phone')->nullable();
            $table->text('site_address')->nullable();
            $table->string('site_facebook')->nullable();
            $table->string('site_instagram')->nullable();
            $table->string('site_twitter')->nullable();
            $table->string('site_linkedin')->nullable();
            $table->text('site_meta_keywords')->nullable();
            $table->text('site_meta_description')->nullable();
            $table->string('site_google_analytics')->nullable();
            $table->timestamps();
        });

        // Insert default record
        DB::table('site_settings')->insert([
            'site_name' => 'PLOTTER.MD',
            'site_description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
