<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesTableSeeder extends Seeder
{
    /**
     * Roles list using stable machine-friendly names.
     */
    public const ROLES = [
        'root',
        'gerencia',
        'administracion_general',
        'administracion_zonal',
        'cajero',
        'asistente',
        'cliente',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        foreach (self::ROLES as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Assign permissions per role
        $allPermissions = Permission::pluck('name')->toArray();

        // root -> all permissions
        $root = Role::where('name', 'root')->first();
        if ($root) {
            $root->syncPermissions($allPermissions);
        }

        // gerencia -> most permissions
        $gerencia = Role::where('name', 'gerencia')->first();
        if ($gerencia) {
            $gerencia->syncPermissions($allPermissions);
        }

        // administracion_general -> manage core modules
        $ag = Role::where('name', 'administracion_general')->first();
        if ($ag) {
            $ag->syncPermissions([
                'view products','create products','edit products','delete products',
                'view sales','create sales','view buys','create buys','manage inventory','view reports'
            ]);
        }

        // administracion_zonal -> subset
        $az = Role::where('name', 'administracion_zonal')->first();
        if ($az) {
            $az->syncPermissions([
                'view products','view sales','view buys','view reports'
            ]);
        }

        // cajero -> sales related
        $cajero = Role::where('name', 'cajero')->first();
        if ($cajero) {
            $cajero->syncPermissions(['view products','create sales','view sales']);
        }

        // asistente -> limited view
        $asistente = Role::where('name', 'asistente')->first();
        if ($asistente) {
            $asistente->syncPermissions(['view products','view sales']);
        }

        // cliente -> view products, create sales (if applicable)
        $cliente = Role::where('name', 'cliente')->first();
        if ($cliente) {
            $cliente->syncPermissions(['view products','create sales']);
        }
    }
}
