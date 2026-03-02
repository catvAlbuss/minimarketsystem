<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add the new "cliente" value to the enum set and ensure order matches
        // the original migration so that fresh clones don't need this file.
        // We use a raw statement because changing enums via Blueprint requires
        // the doctrine/dbal package and is fragile in some MySQL versions.
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('root','managment','administrator_general','logistic_general','administrator','logistic','cashier','asistente','cliente') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // revert to the previous set (without cliente)
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('root','managment','administrator_general','logistic_general','administrator','logistic','cashier','asistente') NOT NULL");
    }
};
