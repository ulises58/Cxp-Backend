<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

final class TenantUsersApiTest extends ApiV1TestCase
{
    public function test_guest_unauthorized_on_users_index(): void
    {
        $this->getJson($this->v1('users'))->assertUnauthorized();
    }

    public function test_member_without_users_view_any_cannot_list(): void
    {
        $tenant = $this->createTenant();
        $member = $this->createTenantUser($tenant, 'user');

        $this->actingAsTenant($member)
            ->getJson($this->v1('users'))
            ->assertForbidden();
    }

    public function test_owner_lists_users_and_excludes_self_from_results(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner', ['email' => 'owner@test.test']);
        $this->createTenantUser($tenant, 'user', ['email' => 'peer@test.test']);

        $res = $this->actingAsTenant($owner)->getJson($this->v1('users'));

        $res->assertOk();
        $emails = collect($res->json('data'))->pluck('email')->all();
        $this->assertContains('peer@test.test', $emails);
        $this->assertNotContains('owner@test.test', $emails);
    }

    public function test_owner_shows_peer_user(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner');
        $peer = $this->createTenantUser($tenant, 'user', ['email' => 'peer@test.test']);

        $this->actingAsTenant($owner)
            ->getJson($this->v1('users/'.$peer->id))
            ->assertOk()
            ->assertJsonPath('data.email', 'peer@test.test');
    }

    public function test_owner_syncs_roles_for_peer(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner');
        $peer = $this->createTenantUser($tenant, 'user', ['email' => 'peer@test.test']);

        $res = $this->actingAsTenant($owner)
            ->patchJson($this->v1('users/'.$peer->id.'/roles'), [
                'roles' => ['admin'],
            ]);

        $res->assertOk();
        $this->assertEquals(['admin'], $res->json('data.roles'));
    }

    public function test_owner_cannot_sync_unknown_roles(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner');
        $peer = $this->createTenantUser($tenant, 'user');

        $this->actingAsTenant($owner)
            ->patchJson($this->v1('users/'.$peer->id.'/roles'), [
                'roles' => ['nope-role'],
            ])
            ->assertUnprocessable();
    }
}
