<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Group;

final class TenantGroupsApiTest extends ApiV1TestCase
{
    public function test_guest_unauthorized_on_groups(): void
    {
        $this->getJson($this->v1('groups'))->assertUnauthorized();
    }

    public function test_member_can_list_and_show_groups_but_not_mutate(): void
    {
        $tenant = $this->createTenant();
        $member = $this->createTenantUser($tenant, 'user');
        $owner = $this->createTenantUser($tenant, 'owner');

        $groupId = $this->actingAsTenant($owner)
            ->postJson($this->v1('groups'), [
                'name' => 'Región demo',
                'description' => null,
                'is_active' => true,
            ])
            ->assertCreated()
            ->json('data.id');

        $this->actingAsTenant($member)
            ->getJson($this->v1('groups'))
            ->assertOk();

        $this->actingAsTenant($member)
            ->getJson($this->v1('groups/'.$groupId))
            ->assertOk()
            ->assertJsonPath('data.name', 'Región demo');

        $this->actingAsTenant($member)
            ->postJson($this->v1('groups'), ['name' => 'No permitido', 'is_active' => true])
            ->assertForbidden();

        $this->actingAsTenant($member)
            ->patchJson($this->v1('groups/'.$groupId), ['name' => 'Hacked'])
            ->assertForbidden();

        $this->actingAsTenant($member)
            ->deleteJson($this->v1('groups/'.$groupId))
            ->assertForbidden();
    }

    public function test_owner_full_group_crud_lifecycle(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner');

        $create = $this->actingAsTenant($owner)
            ->postJson($this->v1('groups'), [
                'name' => 'Grupo inicial',
                'description' => 'Nota',
                'is_active' => true,
            ]);

        $create->assertCreated();
        $id = $create->json('data.id');

        $this->actingAsTenant($owner)
            ->getJson($this->v1('groups/'.$id))
            ->assertOk()
            ->assertJsonPath('data.description', 'Nota');

        $this->actingAsTenant($owner)
            ->patchJson($this->v1('groups/'.$id), [
                'name' => 'Grupo renombrado',
                'description' => null,
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Grupo renombrado')
            ->assertJsonPath('data.is_active', false);

        $this->actingAsTenant($owner)
            ->deleteJson($this->v1('groups/'.$id))
            ->assertNoContent();

        $this->assertNull(Group::query()->find($id));
    }

    public function test_owner_cannot_access_foreign_group_by_id(): void
    {
        $tenantA = $this->createTenant();
        $tenantB = $this->createTenant();
        $ownerA = $this->createTenantUser($tenantA, 'owner');
        $ownerB = $this->createTenantUser($tenantB, 'owner');

        $groupBId = $this->actingAsTenant($ownerB)
            ->postJson($this->v1('groups'), ['name' => 'Solo B', 'is_active' => true])
            ->json('data.id');

        $this->actingAsTenant($ownerA)
            ->getJson($this->v1('groups/'.$groupBId))
            ->assertNotFound();
    }

    public function test_owner_cannot_update_foreign_group(): void
    {
        $tenantA = $this->createTenant();
        $tenantB = $this->createTenant();
        $ownerA = $this->createTenantUser($tenantA, 'owner');
        $ownerB = $this->createTenantUser($tenantB, 'owner');

        $groupBId = $this->actingAsTenant($ownerB)
            ->postJson($this->v1('groups'), ['name' => 'B', 'is_active' => true])
            ->json('data.id');

        $this->actingAsTenant($ownerA)
            ->patchJson($this->v1('groups/'.$groupBId), ['name' => 'X'])
            ->assertNotFound();
    }
}
