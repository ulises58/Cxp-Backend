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

        $tenant = Tenant::query()->create([
            'slug' => 'demo',
            'name' => 'Organización demo',
        ]);

        BootstrapTenantDefaultRoles::run($tenant);

        setPermissionsTeamId(config('permission.platform_team_id'));

        $landlord = User::query()->create([
            'name' => 'Super Admin',
            'email' => 'landlord@cxp.test',
            'password' => Hash::make('password'),
            'tenant_id' => null,
        ]);
        $landlord->assignRole('super_admin');

        setPermissionsTeamId($tenant->getKey());

        $tenantUser = User::query()->create([
            'name' => 'Usuario tenant',
            'email' => 'user@cxp.test',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
        ]);
        $tenantUser->assignRole('tenant_owner');

        setPermissionsTeamId(null);
    }
}
