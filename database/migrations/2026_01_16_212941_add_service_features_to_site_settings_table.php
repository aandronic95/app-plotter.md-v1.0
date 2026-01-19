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
            $table->string('service_feature_1_icon')->nullable()->after('header_contact_3_email')->default('Zap')->comment('Icon pentru serviciu 1 (Rapiditate)');
            $table->string('service_feature_1_title')->nullable()->after('service_feature_1_icon')->default('RAPIDITATE')->comment('Titlu serviciu 1');
            $table->text('service_feature_1_description')->nullable()->after('service_feature_1_title')->comment('Descriere serviciu 1');
            
            $table->string('service_feature_2_icon')->nullable()->after('service_feature_1_description')->default('Settings')->comment('Icon pentru serviciu 2 (Suport în Alegere)');
            $table->string('service_feature_2_title')->nullable()->after('service_feature_2_icon')->default('SUPORT ÎN ALEGERE')->comment('Titlu serviciu 2');
            $table->text('service_feature_2_description')->nullable()->after('service_feature_2_title')->comment('Descriere serviciu 2');
            
            $table->string('service_feature_3_icon')->nullable()->after('service_feature_2_description')->default('Truck')->comment('Icon pentru serviciu 3 (Transport)');
            $table->string('service_feature_3_title')->nullable()->after('service_feature_3_icon')->default('TRANSPORT')->comment('Titlu serviciu 3');
            $table->text('service_feature_3_description')->nullable()->after('service_feature_3_title')->comment('Descriere serviciu 3');
            
            $table->string('service_feature_4_icon')->nullable()->after('service_feature_3_description')->default('Smile')->comment('Icon pentru serviciu 4 (Calitate Garantată)');
            $table->string('service_feature_4_title')->nullable()->after('service_feature_4_icon')->default('CALITATE GARANTATĂ')->comment('Titlu serviciu 4');
            $table->text('service_feature_4_description')->nullable()->after('service_feature_4_title')->comment('Descriere serviciu 4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'service_feature_1_icon',
                'service_feature_1_title',
                'service_feature_1_description',
                'service_feature_2_icon',
                'service_feature_2_title',
                'service_feature_2_description',
                'service_feature_3_icon',
                'service_feature_3_title',
                'service_feature_3_description',
                'service_feature_4_icon',
                'service_feature_4_title',
                'service_feature_4_description',
            ]);
        });
    }
};
