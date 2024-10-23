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
        Schema::create('recyclings', function (Blueprint $table) {
            $table->id();
            $table->string('month'); // Aylık bazda kayıt için
            $table->string('material_type'); // Malzeme türü
            $table->year('year'); // Yıl
            $table->integer('recycling_quantity'); // Geri dönüşüm miktarı
            $table->timestamps(); // Oluşturulma ve güncellenme zamanları
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recyclings');
    }
};
