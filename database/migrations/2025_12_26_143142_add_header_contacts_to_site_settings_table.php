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
            $table->string('header_contact_1_phone')->nullable()->after('site_address')->comment('Telefon contact 1 (Sales)');
            $table->string('header_contact_1_email')->nullable()->after('header_contact_1_phone')->comment('Email contact 1 (Sales)');
            $table->string('header_contact_2_phone')->nullable()->after('header_contact_1_email')->comment('Telefon contact 2 (Info)');
            $table->string('header_contact_2_email')->nullable()->after('header_contact_2_phone')->comment('Email contact 2 (Info)');
            $table->string('header_contact_3_phone')->nullable()->after('header_contact_2_email')->comment('Telefon contact 3 (Office)');
            $table->string('header_contact_3_email')->nullable()->after('header_contact_3_phone')->comment('Email contact 3 (Office)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'header_contact_1_phone',
                'header_contact_1_email',
                'header_contact_2_phone',
                'header_contact_2_email',
                'header_contact_3_phone',
                'header_contact_3_email',
            ]);
        });
    }
};
