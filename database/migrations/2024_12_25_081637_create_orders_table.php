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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade'); // Müşteri ID'si (foreign key)
            $table->timestamp('order_date')->nullable();
            $table->enum('status', ['Sipariş alındı', 'Hazırlanıyor', 'Teslim edildi'])
                ->default('sipariş alındı'); // Sipariş durumu
            $table->text('notes')->nullable(); // Ek notlar (isteğe bağlı)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
