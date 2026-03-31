<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

final class TenantRolesApiTest extends ApiV1TestCase
{
    public function test_guest_unauthorized(): void
    {
        $this->getJson($this->v1('roles'))->assertUnauthorized();
    }

    public function test_member_without_roles_manage_cannot_list(): void
    {
        $tenant = $this->createTenant();
        $member = $this->createTenantUser($tenant, 'user');

        $this->actingAsTenant($member)
            ->getJson($this->v1('roles'))
            ->assertForbidden();
    }

    public function test_owner_lists_roles(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner');

        $res = $this->actingAsTenant($owner)->getJson($this->v1('roles'));

        $res->assertOk();
        $names = collect($res->json('data'))->pluck('name')->all();
        $this->assertContains('owner', $names);
    }

    public function test_owner_creates_shows_updates_and_deletes_custom_role(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner');

        $create = $this->actingAsTenant($owner)
            ->postJson($this->v1('roles'), [
                'name' => 'support_agent',
                'permissions' => ['access', 'sites.read'],
            ]);

        $create->assertCreated();
        $roleId = $create->json('data.id');
        $this->actingAsTenant($owner)
            ->getJson($this->v1('roles/'.$roleId))
            ->assertOk()
            ->assertJsonPath('data.name', 'support_agent');

        $this->actingAsTenant($owner)
            ->patchJson($this->v1('roles/'.$roleId), [
                'permissions' => ['access', 'sites.view-any', 'sites.read'],
            ])
            ->assertOk();

        $this->actingAsTenant($owner)
            ->deleteJson($this->v1('roles/'.$roleId))
            ->assertNoContent();
    }

    public function test_owner_cannot_delete_builtin_owner_role(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner');

        $roles = $this->actingAsTenant($owner)
            ->getJson($this->v1('roles'))
            ->json('data');
        $ownerRole = collect($roles)->firstWhere('name', 'owner');
        $this->assertNotNull($ownerRole);

        $this->actingAsTenant($owner)
            ->deleteJson($this->v1('roles/'.$ownerRole['id']))
            ->assertStatus(422);
    }

    public function test_owner_validates_reserved_role_name_on_create(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner');

        $this->actingAsTenant($owner)
            ->postJson($this->v1('roles'), [
                'name' => 'owner',
                'permissions' => ['access'],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }
}
