<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

final class AuthLoginTest extends ApiV1TestCase
{
    public function test_login_rejects_invalid_password(): void
    {
        $tenant = $this->createTenant();
        $this->createTenantUser($tenant, 'owner', ['email' => 'owner@test.test']);

        $this->postJson($this->v1('auth/login'), [
            'email' => 'owner@test.test',
            'password' => 'wrong-password',
        ])->assertUnauthorized()
            ->assertJsonFragment(['message' => __('auth.failed')]);
    }

    public function test_login_rejects_unknown_email(): void
    {
        $this->postJson($this->v1('auth/login'), [
            'email' => 'nobody@test.test',
            'password' => 'password',
        ])->assertUnauthorized();
    }

    public function test_login_validates_payload(): void
    {
        $this->postJson($this->v1('auth/login'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_accepts_tenant_owner(): void
    {
        $tenant = $this->createTenant();
        $this->createTenantUser($tenant, 'owner', ['email' => 'owner@test.test']);

        $res = $this->postJson($this->v1('auth/login'), [
            'email' => 'owner@test.test',
            'password' => 'password',
        ]);

        $res->assertOk()
            ->assertJsonPath('data.context', 'tenant')
            ->assertJsonPath('data.user.email', 'owner@test.test')
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'permissions'],
                    'tenant' => ['id', 'slug'],
                    'context',
                ],
            ]);
    }

    public function test_login_accepts_landlord(): void
    {
        $this->createLandlordUser(['email' => 'boss@test.test']);

        $this->postJson($this->v1('auth/login'), [
            'email' => 'boss@test.test',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('data.context', 'landlord')
            ->assertJsonPath('data.tenant', null);
    }

    public function test_login_rejects_member_of_inactive_tenant(): void
    {
        $tenant = $this->createTenant(['is_active' => false]);
        $this->createTenantUser($tenant, 'owner', ['email' => 'frozen@test.test']);

        $this->postJson($this->v1('auth/login'), [
            'email' => 'frozen@test.test',
            'password' => 'password',
        ])->assertForbidden()
            ->assertJsonFragment(['message' => __('api.tenant_inactive')]);
    }
}
