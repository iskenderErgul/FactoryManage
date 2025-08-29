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
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->string('supplied_product');
            $table->string('supplied_product_quantity');
            $table->decimal('supplied_product_price', 15, 2);
            $table->date('supply_date');
            $table->enum('payment_method', ['peşin', 'borç', 'kısmi'])->default('borç');
            $table->decimal('paid_amount', 15, 2)->nullable()->comment('Kısmi ödeme durumunda ödenen miktar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplies');
    }
};
