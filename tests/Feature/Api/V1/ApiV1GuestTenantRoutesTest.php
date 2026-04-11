<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

/**
 * Sin token: rutas tenant y landlord protegidas deben responder 401.
 */
final class ApiV1GuestTenantRoutesTest extends ApiV1TestCase
{
    public function test_guest_unauthorized_on_core_tenant_routes(): void
    {
        $paths = [
            'profile',
            'permissions',
            'roles',
            'users',
            'groups',
            'sites',
        ];

        foreach ($paths as $path) {
            $this->getJson($this->v1($path))->assertUnauthorized();
        }
    }

    public function test_guest_unauthorized_on_nested_routes_with_fake_ids(): void
    {
        $this->getJson($this->v1('roles/1'))->assertUnauthorized();
        $this->getJson($this->v1('users/1'))->assertUnauthorized();
        $this->getJson($this->v1('groups/1'))->assertUnauthorized();
        $this->getJson($this->v1('sites/1'))->assertUnauthorized();
        $this->getJson($this->v1('sites/1/locations'))->assertUnauthorized();
        $this->getJson($this->v1('sites/1/locations/1'))->assertUnauthorized();
    }

    public function test_guest_unauthorized_on_landlord_routes(): void
    {
        $this->getJson($this->v1('landlord/health'))->assertUnauthorized();
        $this->getJson($this->v1('landlord/tenants'))->assertUnauthorized();
    }
}
