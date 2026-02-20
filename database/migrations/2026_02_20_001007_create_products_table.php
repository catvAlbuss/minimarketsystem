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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_categories')->references('id')->on('categories')->onDelete('cascade');
            $table->string('code');
            $table->decimal('unit_price');
            $table->decimal('higher_price');
            $table->integer('stock');
            $table->date('expiration_date');
            $table->integer('promotion_discount');
            $table->enum('state',['active', 'inactive']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
