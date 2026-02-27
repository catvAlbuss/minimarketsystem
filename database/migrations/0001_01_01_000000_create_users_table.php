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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // $table->integer('id_sede');
            $table->string('name');
            $table->string('lastname')->default();
            $table->integer('dni');
            $table->integer('phone');
            $table->string('address');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('children');
            $table->enum('affiliate',['ONP', 'AFP'])->default('AFP');
            $table->enum('insured',['EsSalud','SIS'])->default('EsSalud');
            $table->enum('work_modality',['fullTime', 'partTime'])->default('fullTime');
            $table->timestamp('entry_date');
            $table->enum('retention',['yes','no']);
            $table->enum('entry_to_payroll',['yes','no']);
            $table->enum('role',['root', 'managment', 'administrator_general', 'logistic_general','administrator','logistic' ,'cashier','asistente']);
            $table->enum('state', ['active', 'inactive']);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
