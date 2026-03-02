<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

class SystemBranchSeeder extends Seeder
{
    public function run(): void
    {
        $systemUser = User::where('email', 'systemcashier@minimarket.local')->first();

        if (! $systemUser) {
            return;
        }

        Branch::firstOrCreate(
            ['name' => 'Sucursal General'],
            [
                'id_users' => $systemUser->id,
                'address' => 'Dirección General',
                'opening_time' => '08:00:00',
                'closing_time' => '22:00:00',
                'state' => 'active',
            ]
        );
    }
}
