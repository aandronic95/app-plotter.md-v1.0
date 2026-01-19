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
        Schema::table('product_category_showcases', function (Blueprint $table) {
            // Add new section button fields
            $table->string('section_button_text')->nullable()->after('section_description');
            $table->string('section_button_link')->nullable()->after('section_button_text');
            
            // Remove unused title and description fields
            $table->dropColumn(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_category_showcases', function (Blueprint $table) {
            // Restore title and description
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            
            // Remove section button fields
            $table->dropColumn(['section_button_text', 'section_button_link']);
        });
    }
};
