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
            $table->string('hero_banner_headline')->nullable()->after('service_feature_4_description')->comment('Headline pentru hero banner');
            $table->string('hero_banner_title')->nullable()->after('hero_banner_headline')->default('PRINTĂM')->comment('Titlu principal hero banner');
            $table->text('hero_banner_description')->nullable()->after('hero_banner_title')->comment('Descriere hero banner');
            $table->json('hero_banner_features')->nullable()->after('hero_banner_description')->comment('Lista de features (array de string-uri)');
            $table->string('hero_banner_button1_text')->nullable()->after('hero_banner_features')->comment('Text pentru butonul 1');
            $table->string('hero_banner_button1_link')->nullable()->after('hero_banner_button1_text')->comment('Link pentru butonul 1');
            $table->string('hero_banner_button2_text')->nullable()->after('hero_banner_button1_link')->comment('Text pentru butonul 2');
            $table->string('hero_banner_button2_link')->nullable()->after('hero_banner_button2_text')->comment('Link pentru butonul 2');
            $table->string('hero_banner_image')->nullable()->after('hero_banner_button2_link')->comment('Path către imaginea hero banner');
            $table->boolean('hero_banner_is_active')->default(true)->after('hero_banner_image')->comment('Status activ/inactiv pentru hero banner');
            $table->integer('hero_banner_sort_order')->default(0)->after('hero_banner_is_active')->comment('Ordinea de sortare');
            $table->json('hero_banner_rotating_words')->nullable()->after('hero_banner_sort_order')->comment('Lista de cuvinte rotative pentru animație (array de string-uri)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'hero_banner_headline',
                'hero_banner_title',
                'hero_banner_description',
                'hero_banner_features',
                'hero_banner_button1_text',
                'hero_banner_button1_link',
                'hero_banner_button2_text',
                'hero_banner_button2_link',
                'hero_banner_image',
                'hero_banner_is_active',
                'hero_banner_sort_order',
                'hero_banner_rotating_words',
            ]);
        });
    }
};
