<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class GuestCustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::firstOrCreate(
            ['email' => 'guest@minimarket.local'],
            [
                'dni' => '00000000',
                'name' => 'Cliente',
                'last_name' => 'Invitado',
                'birthday' => '2000-01-01',
                'phone' => '900000001',
                'address' => 'Sin dirección',
                'score' => 0,
                'state' => 'active',
            ]
        );
    }
}
