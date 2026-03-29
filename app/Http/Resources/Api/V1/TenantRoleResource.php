<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Application\Tenant\TenantRoleService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Role;

/**
 * @mixin Role
 */
class TenantRoleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'is_builtin' => in_array($this->name, TenantRoleService::BUILTIN_NAMES, true),
            'permissions' => $this->whenLoaded(
                'permissions',
                fn () => $this->permissions->pluck('name')->sort()->values()->all()
            ),
        ];
    }
}
