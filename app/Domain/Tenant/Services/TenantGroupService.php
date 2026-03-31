<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Services;

use App\Domain\Tenant\Repositories\GroupRepository;
use App\Models\Group;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class TenantGroupService
{
    public function __construct(
        private readonly GroupRepository $groups,
    ) {}

    public function paginate(int $perPage): LengthAwarePaginator
    {
        return $this->groups->paginateForTenant($this->tenantId(), $perPage);
    }

    public function create(string $name, ?string $description, bool $isActive): Group
    {
        return $this->groups->create([
            'tenant_id' => $this->tenantId(),
            'name' => $name,
            'description' => $description,
            'is_active' => $isActive,
        ]);
    }

    public function update(Group $group, string $name, ?string $description, bool $isActive): Group
    {
        $this->assertSameTenant($group);

        $group->name = $name;
        $group->description = $description;
        $group->is_active = $isActive;
        $group->save();

        return $group->refresh();
    }

    public function delete(Group $group): void
    {
        $this->assertSameTenant($group);
        $group->delete();
    }

    private function assertSameTenant(Group $group): void
    {
        if (! $this->groups->belongsToTenant($group, $this->tenantId())) {
            abort(404);
        }
    }

    private function tenantId(): string
    {
        return (string) getPermissionsTeamId();
    }
}
