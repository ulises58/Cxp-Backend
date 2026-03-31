<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

final class TenantPermissionsCatalogTest extends ApiV1TestCase
{
    public function test_guest_unauthorized(): void
    {
        $this->getJson($this->v1('permissions'))->assertUnauthorized();
    }

    public function test_owner_can_list_catalog(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner');

        $res = $this->actingAsTenant($owner)
            ->getJson($this->v1('permissions'));

        $res->assertOk();
        $names = collect($res->json('data'))->pluck('name')->all();
        $this->assertContains('access', $names);
        $this->assertContains('sites.view-any', $names);
    }

    public function test_member_without_roles_manage_is_forbidden(): void
    {
        $tenant = $this->createTenant();
        $member = $this->createTenantUser($tenant, 'user');

        $this->actingAsTenant($member)
            ->getJson($this->v1('permissions'))
            ->assertForbidden();
    }
}
