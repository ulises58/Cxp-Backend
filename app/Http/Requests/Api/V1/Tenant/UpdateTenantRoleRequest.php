<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Tenant;

use App\Domain\Shared\Enums\CxpPermission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'string',
                'regex:/^[a-z][a-z0-9_-]{0,63}$/',
            ],
            'permissions' => ['sometimes', 'array', 'min:1'],
            'permissions.*' => ['string', Rule::in(CxpPermission::tenantRoleCatalogValues())],
        ];
    }
}
