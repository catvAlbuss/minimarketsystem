<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    public const PERMISSIONS = [
        'view products', 'create products', 'edit products', 'delete products',
        'view sales', 'create sales', 'edit sales', 'delete sales',
        'view buys', 'create buys', 'edit buys', 'delete buys',
        'manage users', 'manage roles', 'manage permissions',
        'manage branches', 'manage categories', 'manage providers', 'manage promotions',
        'manage inventory', 'view reports'
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::PERMISSIONS as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
    }
}
