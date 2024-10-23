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
        Schema::create('pacs_entries_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pacs_entry_id')->constrained('pacs_entries')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('action', ['create', 'update', 'delete']);
            $table->text('changes')->nullable(); // JSON veya metin olarak değişiklikleri saklar
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacs_entries_log');
    }
};
