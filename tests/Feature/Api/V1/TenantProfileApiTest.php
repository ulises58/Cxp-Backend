<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

final class TenantProfileApiTest extends ApiV1TestCase
{
    public function test_guest_unauthorized(): void
    {
        $this->getJson($this->v1('profile'))->assertUnauthorized();
    }

    public function test_landlord_cannot_access_tenant_profile_without_tenant_context(): void
    {
        $landlord = $this->createLandlordUser();

        $this->actingAsLandlord($landlord)
            ->getJson($this->v1('profile'))
            ->assertForbidden()
            ->assertJsonFragment(['message' => __('api.tenant_context_required')]);
    }

    public function test_tenant_owner_receives_profile(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner', ['email' => 'me@test.test']);

        $this->actingAsTenant($owner)
            ->getJson($this->v1('profile'))
            ->assertOk()
            ->assertJsonPath('data.user.email', 'me@test.test')
            ->assertJsonPath('data.user.tenant_id', $tenant->id)
            ->assertJsonPath('data.tenant.id', $tenant->id);
    }

    public function test_inactive_tenant_member_is_blocked_by_middleware(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createTenantUser($tenant, 'owner');
        $tenant->is_active = false;
        $tenant->save();

        $this->actingAsTenant($owner)
            ->getJson($this->v1('profile'))
            ->assertForbidden()
            ->assertJsonFragment(['message' => __('api.tenant_inactive')]);
    }
}
