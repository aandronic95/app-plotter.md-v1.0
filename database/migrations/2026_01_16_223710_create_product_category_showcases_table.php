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
        Schema::create('product_category_showcases', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); // "TIPURI DE PRODUSE"
            $table->text('description')->nullable();
            $table->string('section_title')->nullable(); // Titlul secțiunii principale
            $table->text('section_description')->nullable(); // Descrierea secțiunii
            $table->string('button_text')->default('VEZI TOT CATALOGUL >');
            $table->string('button_link')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('image')->nullable(); // Imaginea pentru card
            $table->string('name')->nullable(); // Numele cardului (ex: "Sacoșe", "Pachete")
            $table->string('subtitle')->nullable(); // Subtitlul (ex: "Sub Title")
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_category_showcases');
    }
};
