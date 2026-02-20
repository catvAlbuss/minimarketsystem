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
        Schema::create('buys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_provider')->references('id')->on('providers')->onDelete('cascade');
            $table->foreignId('id_users')->references('id')->on('users')->onDelete('cascade');
            $table->string('voucher_number');
            $table->decimal('total');
            $table->enum('payment_method',['cash','card','yape', 'plin']);
            $table->enum('payment_status',['cancelled','pending','delivered']);
            $table->timestamp('date_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buys');
    }
};
