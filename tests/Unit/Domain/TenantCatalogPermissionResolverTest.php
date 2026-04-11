<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Shared\Enums\CxpPermission;
use App\Domain\Tenant\TenantCatalogPermissionResolver;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

final class TenantCatalogPermissionResolverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_resolves_valid_permission_names(): void
    {
        $resolver = new TenantCatalogPermissionResolver;
        $names = [CxpPermission::Access->value, CxpPermission::SitesRead->value];

        $collection = $resolver->resolve($names);

        $this->assertCount(2, $collection);
        $this->assertEqualsCanonicalizing(
            $names,
            $collection->pluck('name')->all(),
        );
    }

    public function test_rejects_permission_not_in_catalog(): void
    {
        $resolver = new TenantCatalogPermissionResolver;

        $this->expectException(ValidationException::class);

        $resolver->resolve(['landlord.panel']);
    }

    public function test_rejects_unknown_permission_string(): void
    {
        $resolver = new TenantCatalogPermissionResolver;

        $this->expectException(ValidationException::class);

        $resolver->resolve(['totally.fake.permission']);
    }

    public function test_rejects_when_permission_missing_in_database(): void
    {
        Permission::query()
            ->where('name', CxpPermission::Access->value)
            ->where('guard_name', 'sanctum')
            ->delete();

        $resolver = new TenantCatalogPermissionResolver;

        $this->expectException(ValidationException::class);

        $resolver->resolve([CxpPermission::Access->value]);
    }
}
