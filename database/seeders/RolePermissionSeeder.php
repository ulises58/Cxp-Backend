<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Shared\Enums\CxpPermission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'sanctum';

        foreach (CxpPermission::allSeederPermissionValues() as $name) {
            Permission::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => $guard,
            ]);
        }

        $landlordValues = array_map(
            fn (CxpPermission $p) => $p->value,
            CxpPermission::landlordPlatform(),
        );

        setPermissionsTeamId(config('permission.platform_team_id'));

        $superAdmin = Role::findOrCreate('super_admin', $guard);
        $superAdmin->syncPermissions(
            Permission::query()->where('guard_name', $guard)->whereIn('name', $landlordValues)->get()
        );

        setPermissionsTeamId(null);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
