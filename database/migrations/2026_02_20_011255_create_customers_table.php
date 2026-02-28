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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('dni', 8)->unique();
            $table->string('name');
            $table->string('last_name');
            $table->date('birthday');
            $table->string('email')->unique();
            $table->string('phone', 9);
            $table->string('address');
            $table->integer('score')->default(0);
            $table->enum('state', ['active', 'inactive'])->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
