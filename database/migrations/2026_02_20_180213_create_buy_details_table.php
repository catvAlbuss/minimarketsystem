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
        Schema::create('buy_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_buys')->references('id')->on('buys')->onDelete('cascade');
            $table->foreignId('id_products')->references('id')->on('products')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price');
            $table->decimal('sub_total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buy_details');
    }
};
