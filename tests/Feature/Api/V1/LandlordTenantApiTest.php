<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Tenant;

final class LandlordTenantApiTest extends ApiV1TestCase
{
    public function test_landlord_lists_tenants(): void
    {
        $this->createTenant(['slug' => 'acme-a', 'name' => 'Acme A']);
        $this->createTenant(['slug' => 'acme-b', 'name' => 'Acme B']);
        $landlord = $this->createLandlordUser();

        $res = $this->actingAsLandlord($landlord)
            ->getJson($this->v1('landlord/tenants'));

        $res->assertOk();
        $this->assertGreaterThanOrEqual(2, count($res->json('data')));
    }

    public function test_landlord_creates_shows_updates_and_deletes_tenant(): void
    {
        $landlord = $this->createLandlordUser();
        $slug = 'new-org-'.uniqid();

        $create = $this->actingAsLandlord($landlord)
            ->postJson($this->v1('landlord/tenants'), [
                'slug' => $slug,
                'name' => 'New org',
                'is_active' => true,
            ]);

        $create->assertCreated()
            ->assertJsonPath('data.slug', $slug);
        $id = $create->json('data.id');
        $this->assertNotEmpty($id);

        $this->actingAsLandlord($landlord)
            ->getJson($this->v1('landlord/tenants/'.$id))
            ->assertOk()
            ->assertJsonPath('data.slug', $slug);

        $this->actingAsLandlord($landlord)
            ->patchJson($this->v1('landlord/tenants/'.$id), [
                'name' => 'Renamed',
                'is_active' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed');

        $this->actingAsLandlord($landlord)
            ->deleteJson($this->v1('landlord/tenants/'.$id))
            ->assertNoContent();

        $this->assertNull(Tenant::query()->find($id));
    }

    public function test_landlord_validates_duplicate_slug_on_create(): void
    {
        $this->createTenant(['slug' => 'dup-slug']);
        $landlord = $this->createLandlordUser();

        $this->actingAsLandlord($landlord)
            ->postJson($this->v1('landlord/tenants'), [
                'slug' => 'dup-slug',
                'name' => 'Other',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_tenant_user_cannot_access_landlord_tenants(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createTenantUser($tenant, 'owner');

        $this->actingAsTenant($user)
            ->getJson($this->v1('landlord/tenants'))
            ->assertForbidden();
    }
}
