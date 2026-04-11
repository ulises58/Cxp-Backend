<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

final class TenantLocationValidationApiTest extends ApiV1TestCase
{
    public function test_location_create_rejects_invalid_longitude(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner');

        $siteId = $this->actingAsTenant($owner)
            ->postJson($this->v1('sites'), ['name' => 'S', 'is_active' => true])
            ->json('data.id');

        $this->actingAsTenant($owner)
            ->postJson($this->v1('sites/'.$siteId.'/locations'), [
                'name' => 'L',
                'longitude' => 181,
                'is_active' => true,
            ])
            ->assertUnprocessable();
    }

    public function test_location_create_rejects_negative_radius(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner');

        $siteId = $this->actingAsTenant($owner)
            ->postJson($this->v1('sites'), ['name' => 'S', 'is_active' => true])
            ->json('data.id');

        $this->actingAsTenant($owner)
            ->postJson($this->v1('sites/'.$siteId.'/locations'), [
                'name' => 'L',
                'radius' => -1,
                'is_active' => true,
            ])
            ->assertUnprocessable();
    }

    public function test_location_create_rejects_timezone_too_long(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner');

        $siteId = $this->actingAsTenant($owner)
            ->postJson($this->v1('sites'), ['name' => 'S', 'is_active' => true])
            ->json('data.id');

        $this->actingAsTenant($owner)
            ->postJson($this->v1('sites/'.$siteId.'/locations'), [
                'name' => 'L',
                'timezone' => str_repeat('a', 65),
                'is_active' => true,
            ])
            ->assertUnprocessable();
    }

    public function test_location_patch_can_clear_geo_and_timezone(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner');

        $siteId = $this->actingAsTenant($owner)
            ->postJson($this->v1('sites'), ['name' => 'S', 'is_active' => true])
            ->json('data.id');

        $locId = $this->actingAsTenant($owner)
            ->postJson($this->v1('sites/'.$siteId.'/locations'), [
                'name' => 'L',
                'latitude' => 10.5,
                'longitude' => -20.25,
                'radius' => 50,
                'timezone' => 'UTC',
                'is_active' => true,
            ])
            ->json('data.id');

        $this->actingAsTenant($owner)
            ->patchJson($this->v1('sites/'.$siteId.'/locations/'.$locId), [
                'latitude' => null,
                'longitude' => null,
                'radius' => null,
                'timezone' => null,
            ])
            ->assertOk()
            ->assertJsonPath('data.latitude', null)
            ->assertJsonPath('data.longitude', null)
            ->assertJsonPath('data.radius', null)
            ->assertJsonPath('data.timezone', null);
    }
}
