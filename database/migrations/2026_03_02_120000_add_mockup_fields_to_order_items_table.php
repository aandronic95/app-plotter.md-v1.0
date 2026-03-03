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
            $table->string('mockup_path')->nullable()->after('colturi');
            $table->string('mockup_filename')->nullable()->after('mockup_path');
            $table->boolean('elaborate_mockup')->default(false)->after('mockup_filename');
            $table->decimal('elaborate_mockup_price', 10, 2)->nullable()->after('elaborate_mockup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['mockup_path', 'mockup_filename', 'elaborate_mockup', 'elaborate_mockup_price']);
        });
    }
};
