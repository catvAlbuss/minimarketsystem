<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('id_branches')->nullable()->after('id');
        });

        $branchId = DB::table('branches')
            ->where('state', 'active')
            ->orderBy('id')
            ->value('id');

        if (! $branchId) {
            $userId = DB::table('users')->where('email', 'systemcashier@minimarket.local')->value('id');

            if (! $userId) {
                $now = now();
                $userId = DB::table('users')->insertGetId([
                    'name' => 'System',
                    'lastname' => 'Cashier',
                    'dni' => '99999999',
                    'phone' => '900000000',
                    'address' => 'Sistema',
                    'email' => 'systemcashier@minimarket.local',
                    'password' => bcrypt('password'),
                    'children' => '0',
                    'affiliate' => 'AFP',
                    'insured' => 'EsSalud',
                    'work_modality' => 'fullTime',
                    'entry_date' => $now,
                    'retention' => 'no',
                    'entry_to_payroll' => 'no',
                    'role' => 'cashier',
                    'state' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $now = now();
            $branchId = DB::table('branches')->insertGetId([
                'id_users' => $userId,
                'name' => 'Sucursal General',
                'address' => 'Dirección General',
                'opening_time' => '08:00:00',
                'closing_time' => '22:00:00',
                'state' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('categories')->whereNull('id_branches')->update(['id_branches' => $branchId]);

        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('id_branches')->nullable(false)->change();
            $table->index('id_branches');
            $table->foreign('id_branches')
                ->references('id')
                ->on('branches')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['id_branches']);
            $table->dropIndex(['id_branches']);
            $table->dropColumn('id_branches');
        });
    }
};
