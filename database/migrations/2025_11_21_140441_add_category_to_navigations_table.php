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
        Schema::table('navigations', function (Blueprint $table) {
            $table->string('category')->nullable()->after('group')->comment('Categorie pentru footer: company, customer, legal');
            $table->index(['group', 'is_active', 'category'], 'navigations_group_active_category_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('navigations', function (Blueprint $table) {
            $table->dropIndex('navigations_group_active_category_index');
            $table->dropColumn('category');
        });
    }
};
