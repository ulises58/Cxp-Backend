<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

final class LandlordHealthTest extends ApiV1TestCase
{
    public function test_guest_cannot_access_landlord_health(): void
    {
        $this->getJson($this->v1('landlord/health'))->assertUnauthorized();
    }

    public function test_tenant_user_is_forbidden_on_landlord_health(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createTenantUser($tenant, 'owner');

        $this->actingAsTenant($user)
            ->getJson($this->v1('landlord/health'))
            ->assertForbidden();
    }

    public function test_landlord_health_returns_scope_and_user(): void
    {
        $landlord = $this->createLandlordUser();

        $this->actingAsLandlord($landlord)
            ->getJson($this->v1('landlord/health'))
            ->assertOk()
            ->assertJsonPath('data.scope', 'landlord')
            ->assertJsonPath('data.user.id', $landlord->id)
            ->assertJsonPath('data.user.email', $landlord->email);
    }
}
