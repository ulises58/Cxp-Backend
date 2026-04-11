<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Site;

final class TenantSitesAndLocationsApiTest extends ApiV1TestCase
{
    public function test_guest_unauthorized_on_sites(): void
    {
        $this->getJson($this->v1('sites'))->assertUnauthorized();
    }

    public function test_member_read_only_on_sites_and_locations(): void
    {
        $tenant = $this->createTenant();
        $member = $this->createTenantUser($tenant, 'user');
        $owner = $this->createTenantUser($tenant, 'owner');

        $siteId = $this->actingAsTenant($owner)
            ->postJson($this->v1('sites'), [
                'name' => 'Plant A',
                'description' => null,
                'is_active' => true,
            ])
            ->json('data.id');

        $locId = $this->actingAsTenant($owner)
            ->postJson($this->v1('sites/'.$siteId.'/locations'), [
                'name' => 'Hall',
                'description' => null,
                'metadata' => null,
                'is_active' => true,
            ])
            ->json('data.id');

        $this->actingAsTenant($member)
            ->getJson($this->v1('sites'))
            ->assertOk();

        $this->actingAsTenant($member)
            ->getJson($this->v1('sites/'.$siteId))
            ->assertOk();

        $this->actingAsTenant($member)
            ->postJson($this->v1('sites'), ['name' => 'Nope', 'is_active' => true])
            ->assertForbidden();

        $this->actingAsTenant($member)
            ->getJson($this->v1('sites/'.$siteId.'/locations'))
            ->assertOk();

        $this->actingAsTenant($member)
            ->getJson($this->v1('sites/'.$siteId.'/locations/'.$locId))
            ->assertOk();

        $this->actingAsTenant($member)
            ->getJson($this->v1('groups'))
            ->assertOk();

        $this->actingAsTenant($member)
            ->postJson($this->v1('sites/'.$siteId.'/locations'), [
                'name' => 'Blocked',
                'is_active' => true,
            ])
            ->assertForbidden();
    }

    public function test_owner_full_site_and_location_lifecycle(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner');

        $createSite = $this->actingAsTenant($owner)
            ->postJson($this->v1('sites'), [
                'name' => 'Main site',
                'description' => 'HQ',
                'is_active' => true,
            ]);

        $createSite->assertCreated();
        $siteId = $createSite->json('data.id');

        $this->actingAsTenant($owner)
            ->getJson($this->v1('sites/'.$siteId))
            ->assertOk()
            ->assertJsonPath('data.name', 'Main site');

        $this->actingAsTenant($owner)
            ->patchJson($this->v1('sites/'.$siteId), [
                'name' => 'Main site updated',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $groupId = $this->actingAsTenant($owner)
            ->postJson($this->v1('groups'), [
                'name' => 'North region',
                'description' => 'Ops',
                'is_active' => true,
            ])
            ->assertCreated()
            ->json('data.id');

        $this->actingAsTenant($owner)
            ->patchJson($this->v1('sites/'.$siteId), [
                'group_id' => (int) $groupId,
            ])
            ->assertOk()
            ->assertJsonPath('data.group_id', (string) $groupId)
            ->assertJsonPath('data.group.name', 'North region');

        $createLoc = $this->actingAsTenant($owner)
            ->postJson($this->v1('sites/'.$siteId.'/locations'), [
                'name' => 'Warehouse',
                'description' => 'Back',
                'address' => 'Calle 1',
                'latitude' => 19.4326,
                'longitude' => -99.1332,
                'radius' => 100.5,
                'timezone' => 'America/Mexico_City',
                'metadata' => ['zone' => 'north'],
                'is_active' => true,
            ]);

        $createLoc->assertCreated();
        $locId = $createLoc->json('data.id');

        $this->actingAsTenant($owner)
            ->patchJson($this->v1('sites/'.$siteId.'/locations/'.$locId), [
                'name' => 'Warehouse east',
                'metadata' => ['zone' => 'east'],
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Warehouse east')
            ->assertJsonPath('data.address', 'Calle 1')
            ->assertJsonPath('data.timezone', 'America/Mexico_City');

        $this->actingAsTenant($owner)
            ->deleteJson($this->v1('sites/'.$siteId.'/locations/'.$locId))
            ->assertNoContent();

        $this->actingAsTenant($owner)
            ->deleteJson($this->v1('sites/'.$siteId))
            ->assertNoContent();

        $this->assertNull(Site::query()->find($siteId));
    }

    public function test_owner_cannot_access_foreign_site_by_id(): void
    {
        $tenantA = $this->createTenant();
        $tenantB = $this->createTenant();
        $ownerA = $this->createTenantUser($tenantA, 'owner');
        $ownerB = $this->createTenantUser($tenantB, 'owner');

        $siteBId = $this->actingAsTenant($ownerB)
            ->postJson($this->v1('sites'), ['name' => 'B only', 'is_active' => true])
            ->json('data.id');

        $this->actingAsTenant($ownerA)
            ->getJson($this->v1('sites/'.$siteBId))
            ->assertNotFound();
    }

    public function test_location_create_rejects_invalid_latitude(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner');

        $siteId = $this->actingAsTenant($owner)
            ->postJson($this->v1('sites'), ['name' => 'S1', 'is_active' => true])
            ->json('data.id');

        $this->actingAsTenant($owner)
            ->postJson($this->v1('sites/'.$siteId.'/locations'), [
                'name' => 'Bad geo',
                'latitude' => 91,
                'is_active' => true,
            ])
            ->assertUnprocessable();
    }

    public function test_site_patch_can_clear_group_id(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner');

        $groupId = $this->actingAsTenant($owner)
            ->postJson($this->v1('groups'), ['name' => 'G1', 'is_active' => true])
            ->json('data.id');

        $siteId = $this->actingAsTenant($owner)
            ->postJson($this->v1('sites'), [
                'name' => 'With group',
                'group_id' => (int) $groupId,
                'is_active' => true,
            ])
            ->assertCreated()
            ->json('data.id');

        $this->actingAsTenant($owner)
            ->patchJson($this->v1('sites/'.$siteId), ['group_id' => null])
            ->assertOk()
            ->assertJsonPath('data.group_id', null)
            ->assertJsonPath('data.group', null);
    }

    public function test_owner_cannot_assign_foreign_group_to_site(): void
    {
        $tenantA = $this->createTenant();
        $tenantB = $this->createTenant();
        $ownerA = $this->createTenantUser($tenantA, 'owner');
        $ownerB = $this->createTenantUser($tenantB, 'owner');

        $groupBId = $this->actingAsTenant($ownerB)
            ->postJson($this->v1('groups'), ['name' => 'B group', 'is_active' => true])
            ->json('data.id');

        $this->actingAsTenant($ownerA)
            ->postJson($this->v1('sites'), [
                'name' => 'Bad group ref',
                'is_active' => true,
                'group_id' => (int) $groupBId,
            ])
            ->assertUnprocessable();
    }

    public function test_owner_cannot_access_location_under_wrong_site(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner');

        $site1 = $this->actingAsTenant($owner)
            ->postJson($this->v1('sites'), ['name' => 'Site 1', 'is_active' => true])
            ->json('data.id');

        $site2 = $this->actingAsTenant($owner)
            ->postJson($this->v1('sites'), ['name' => 'Site 2', 'is_active' => true])
            ->json('data.id');

        $locId = $this->actingAsTenant($owner)
            ->postJson($this->v1('sites/'.$site1.'/locations'), [
                'name' => 'Only on site 1',
                'is_active' => true,
            ])
            ->json('data.id');

        $this->actingAsTenant($owner)
            ->getJson($this->v1('sites/'.$site2.'/locations/'.$locId))
            ->assertNotFound();
    }
}
