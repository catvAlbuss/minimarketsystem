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
        'managment',
        'administrator_general',
        'logistic_general',
        'administrator',
        'logistic',
        'cashier',
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

        // managment (formerly "gerencia") -> most permissions
        $managment = Role::where('name', 'managment')->first();
        if ($managment) {
            $managment->syncPermissions($allPermissions);
        }

        // administrator_general -> manage core modules
        $ag = Role::where('name', 'administrator_general')->first();
        if ($ag) {
            $ag->syncPermissions([
                'view products','create products','edit products','delete products',
                'view sales','create sales','view buys','create buys','manage inventory','view reports'
            ]);
        }

        // logistic_general (previously "administracion_zonal") -> limited view access
        $lg = Role::where('name', 'logistic_general')->first();
        if ($lg) {
            $lg->syncPermissions([
                'view products','view sales','view buys','view reports'
            ]);
        }

        // administrator -> no default permissions; customise after seeding if necessary
        $admin = Role::where('name', 'administrator')->first();
        if ($admin) {
            // intentionally left blank
        }

        // logistic -> placeholder for branch-level logistics staff
        $log = Role::where('name', 'logistic')->first();
        if ($log) {
            // intentionally left blank
        }

        // cashier (cajero) -> sales related
        $cashier = Role::where('name', 'cashier')->first();
        if ($cashier) {
            $cashier->syncPermissions(['view products','create sales','view sales']);
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
