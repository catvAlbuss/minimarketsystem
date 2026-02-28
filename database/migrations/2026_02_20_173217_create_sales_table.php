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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_customers')->references('id')->on('customers')->onDelete('cascade');
            $table->foreignId('id_users')->references('id')->on('users')->onDelete('cascade');

            $table->string('voucher_number')->unique();
            $table->decimal('igv', 5, 4)->default(0.18);
            $table->decimal('total', 10, 2);
            $table->enum('payment_method', ['cash', 'card', 'yape', 'plin']);
            $table->enum('voucher', ['ticket', 'invoice']);
            $table->string('document')->nullable();
            $table->timestamp('date_time')->useCurrent();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
