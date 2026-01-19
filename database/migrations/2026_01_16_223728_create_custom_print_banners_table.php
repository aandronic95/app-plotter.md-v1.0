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
        Schema::create('custom_print_banners', function (Blueprint $table) {
            $table->id();
            $table->string('headline')->nullable(); // "Hai să tipărim propriul tău produs !"
            $table->string('title')->nullable(); // Titlu principal
            $table->text('description')->nullable(); // Descrierea
            $table->string('button_text')->default('Tipăreşte macheta mea');
            $table->string('button_link')->nullable();
            $table->string('image')->nullable(); // Imaginea din partea dreaptă
            $table->string('background_color')->default('#f0f0f0'); // Culoarea de fundal
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_print_banners');
    }
};
