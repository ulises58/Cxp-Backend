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

        $createLoc = $this->actingAsTenant($owner)
            ->postJson($this->v1('sites/'.$siteId.'/locations'), [
                'name' => 'Warehouse',
                'description' => 'Back',
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
            ->assertJsonPath('data.name', 'Warehouse east');

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
}
