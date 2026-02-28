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
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_products')->references('id')->on('products')->onDelete('cascade');
            $table->integer('ruc');
            $table->string('company_name');
            $table->string('contact_person');
            $table->integer('phone');
            $table->string('email');
            $table->string('address');
            $table->enum('category', ['wholesaler', 'retailer', 'distributor', 'manufacturer']);
            $table->string('description_products');
            $table->enum('status', ['active', 'inactive']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
