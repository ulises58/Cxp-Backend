<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'is_active' => 'boolean',
        ]);
    }

    public static function getCustomColumns(): array
    {
        // Incluir columnas reales; si no, VirtualColumn mete `id` solo en `data` y MySQL falla.
        return ['id', 'slug', 'name', 'is_active'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id', 'id');
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class, 'tenant_id', 'id');
    }
}
