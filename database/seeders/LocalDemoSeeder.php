<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Application\Tenant\BootstrapTenantDefaultRoles;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LocalDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $tenantSpecs = [
            [
                'slug' => 'demo',
                'name' => 'Organización demo A',
                'user_name' => 'Usuario demo A',
                'user_email' => 'user@cxp.test',
            ],
            [
                'slug' => 'demo-b',
                'name' => 'Organización demo B',
                'user_name' => 'Usuario demo B',
                'user_email' => 'user-b@cxp.test',
            ],
        ];

        $tenants = [];
        foreach ($tenantSpecs as $spec) {
            $tenant = Tenant::query()->create([
                'slug' => $spec['slug'],
                'name' => $spec['name'],
            ]);
            BootstrapTenantDefaultRoles::run($tenant);
            $tenants[] = ['tenant' => $tenant, 'spec' => $spec];
        }

        setPermissionsTeamId(config('permission.platform_team_id'));

        $landlord = User::query()->create([
            'name' => 'Super Admin',
            'email' => 'landlord@cxp.test',
            'password' => Hash::make('password'),
            'tenant_id' => null,
        ]);
        $landlord->assignRole('super_admin');

        foreach ($tenants as $row) {
            $tenant = $row['tenant'];
            $spec = $row['spec'];
            setPermissionsTeamId($tenant->getKey());

            User::query()->create([
                'name' => $spec['user_name'],
                'email' => $spec['user_email'],
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
            ])->assignRole('owner');
        }

        setPermissionsTeamId(null);
    }
}
