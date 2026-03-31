<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Domain\Tenant\Actions\BootstrapTenantDefaultRolesAction;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

abstract class ApiV1TestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    protected function v1(string $path = ''): string
    {
        $path = ltrim($path, '/');

        return $path === '' ? '/api/v1' : '/api/v1/'.$path;
    }

    protected function createTenant(array $overrides = []): Tenant
    {
        $tenant = Tenant::query()->create(array_merge([
            'slug' => 'org-'.uniqid(),
            'name' => 'Test organization',
            'is_active' => true,
        ], $overrides));

        app(BootstrapTenantDefaultRolesAction::class)($tenant);

        return $tenant->fresh();
    }

    protected function createLandlordUser(array $overrides = []): User
    {
        setPermissionsTeamId(config('permission.platform_team_id'));

        $user = User::factory()->create(array_merge([
            'tenant_id' => null,
            'password' => Hash::make('password'),
        ], $overrides));

        $user->assignRole('super_admin');

        setPermissionsTeamId(null);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user->fresh();
    }

    protected function createTenantUser(Tenant $tenant, string $roleName, array $overrides = []): User
    {
        setPermissionsTeamId($tenant->getKey());

        $user = User::factory()->create(array_merge([
            'tenant_id' => $tenant->id,
            'password' => Hash::make('password'),
        ], $overrides));

        $user->assignRole($roleName);

        setPermissionsTeamId(null);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user->fresh();
    }

    protected function actingAsLandlord(User $user): static
    {
        Sanctum::actingAs($user);

        return $this;
    }

    protected function actingAsTenant(User $user): static
    {
        Sanctum::actingAs($user);

        return $this;
    }

    protected function tearDown(): void
    {
        setPermissionsTeamId(null);
        parent::tearDown();
    }
}
