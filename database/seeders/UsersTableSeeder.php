<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Roles that have global access (no branch restriction).
     *
     * Those are the same identifiers as in User::GLOBAL_ROLES.
     */
    private const GLOBAL_ROLES = ['root', 'managment', 'administrator_general'];

    /**
     * Map of seeder role label => [name, email, legacy_role]
     */
    public const USERS = [
        'root'                   => ['name' => 'Root User',      'email' => 'root@example.com'],
        'managment'              => ['name' => 'Gerencia User',  'email' => 'gerencia@example.com'],
        'administrator_general'  => ['name' => 'Admin General',  'email' => 'admingeneral@example.com'],
        'logistic_general'       => ['name' => 'Zonal Admin',    'email' => 'adminzonal@example.com'],
        'administrator'          => ['name' => 'Administrador',  'email' => 'administrador@example.com'],
        'logistic'               => ['name' => 'Logística User', 'email' => 'logistica@example.com'],
        'cashier'                => ['name' => 'Cajero User',    'email' => 'cajero@example.com'],
        'asistente'              => ['name' => 'Asistente User', 'email' => 'asistente@example.com'],
        'cliente'                => ['name' => 'Cliente User',   'email' => 'cliente@example.com'],
    ];

    public function run(): void
    {
        $firstBranch = Branch::first();

        foreach (self::USERS as $role => $data) {
            $isGlobal  = in_array($role, self::GLOBAL_ROLES, true);
            $branchId  = $isGlobal ? null : ($firstBranch?->id ?? null);

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'            => $data['name'],
                    'lastname'        => 'Sin Apellido',
                    'dni'             => rand(10000000, 99999999),
                    'phone'           => rand(900000000, 999999999),
                    'address'         => 'N/A',
                    'password'        => bcrypt('password'),
                    'children'        => '0',
                    'affiliate'       => 'AFP',
                    'insured'         => 'EsSalud',
                    'work_modality'   => 'fullTime',
                    'entry_date'      => now(),
                    'retention'       => 'no',
                    'entry_to_payroll' => 'no',
                    'role'            => $role,
                    'state'           => 'active',
                    'branch_id'       => $branchId,
                ]
            );

            // If user already existed, update branch_id to reflect scoping rules
            if (! $isGlobal && $user->wasRecentlyCreated === false) {
                $user->updateQuietly(['branch_id' => $branchId]);
            }

            // Assign Spatie role (sync to avoid duplicates)
            if (method_exists($user, 'syncRoles')) {
                $user->syncRoles([$role]);
            }
        }

        // System cashier — assign to first branch
        $systemCashier = User::firstOrCreate(
            ['email' => 'systemcashier@minimarket.local'],
            [
                'name'            => 'System',
                'lastname'        => 'Cashier',
                'dni'             => '99999999',
                'phone'           => '900000000',
                'address'         => 'Sistema',
                'password'        => bcrypt('password'),
                'children'        => '0',
                'affiliate'       => 'AFP',
                'insured'         => 'EsSalud',
                'work_modality'   => 'fullTime',
                'entry_date'      => now(),
                'retention'       => 'no',
                'entry_to_payroll' => 'no',
                'role'            => 'cashier',
                'state'           => 'active',
                'branch_id'       => $firstBranch?->id ?? null,
            ]
        );

        if (method_exists($systemCashier, 'syncRoles')) {
            $systemCashier->syncRoles(['cashier']);
        }
    }
}
