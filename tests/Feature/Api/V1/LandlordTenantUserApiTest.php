<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;

final class LandlordTenantUserApiTest extends ApiV1TestCase
{
    public function test_landlord_lists_roles_for_tenant(): void
    {
        $tenant = $this->createTenant();
        $landlord = $this->createLandlordUser();

        $res = $this->actingAsLandlord($landlord)
            ->getJson($this->v1('landlord/tenants/'.$tenant->id.'/roles'));

        $res->assertOk();
        $names = collect($res->json('data'))->pluck('name')->all();
        $this->assertContains('owner', $names);
        $this->assertContains('admin', $names);
        $this->assertContains('user', $names);
    }

    public function test_landlord_lists_users_for_tenant(): void
    {
        $tenant = $this->createTenant();
        $this->createTenantUser($tenant, 'owner', ['email' => 'a@test.test']);
        $this->createTenantUser($tenant, 'user', ['email' => 'b@test.test']);
        $landlord = $this->createLandlordUser();

        $res = $this->actingAsLandlord($landlord)
            ->getJson($this->v1('landlord/tenants/'.$tenant->id.'/users'));

        $res->assertOk();
        $this->assertGreaterThanOrEqual(2, count($res->json('data')));
    }

    public function test_landlord_creates_user_in_tenant(): void
    {
        $tenant = $this->createTenant();
        $landlord = $this->createLandlordUser();

        $this->actingAsLandlord($landlord)
            ->postJson($this->v1('landlord/tenants/'.$tenant->id.'/users'), [
                'name' => 'Provisioned',
                'email' => 'provisioned@test.test',
                'password' => 'secret-pass',
                'password_confirmation' => 'secret-pass',
                'roles' => ['user'],
            ])
            ->assertCreated()
            ->assertJsonPath('data.email', 'provisioned@test.test');

        $this->assertTrue(
            User::query()->where('email', 'provisioned@test.test')->where('tenant_id', $tenant->id)->exists()
        );
    }

    public function test_landlord_validates_unknown_role_when_creating_user(): void
    {
        $tenant = $this->createTenant();
        $landlord = $this->createLandlordUser();

        $this->actingAsLandlord($landlord)
            ->postJson($this->v1('landlord/tenants/'.$tenant->id.'/users'), [
                'name' => 'X',
                'email' => 'x@test.test',
                'password' => 'secret-pass',
                'password_confirmation' => 'secret-pass',
                'roles' => ['non-existent-role'],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['roles.0']);
    }

    public function test_tenant_owner_cannot_use_landlord_user_endpoints(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner');

        $this->actingAsTenant($owner)
            ->getJson($this->v1('landlord/tenants/'.$tenant->id.'/users'))
            ->assertForbidden();
    }
}
