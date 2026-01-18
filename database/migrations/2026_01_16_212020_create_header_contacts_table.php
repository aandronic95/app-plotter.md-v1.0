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
        Schema::create('header_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('Titlul contactului (ex: Sales, Info, Office)');
            $table->string('phone')->nullable()->comment('Număr de telefon');
            $table->string('email')->nullable()->comment('Adresă email');
            $table->integer('order')->default(0)->comment('Ordinea de afișare');
            $table->boolean('is_active')->default(true)->comment('Contact activ/inactiv');
            $table->timestamps();
            
            $table->index('order');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('header_contacts');
    }
};
