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
            $table->string('carousel_banner_image')->nullable()->after('section_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_category_showcases', function (Blueprint $table) {
            $table->dropColumn('carousel_banner_image');
        });
    }
};
