<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Tenant;

use App\Application\Tenant\BootstrapTenantDefaultRoles;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantRoleRequest extends FormRequest
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
                'required',
                'string',
                'regex:/^[a-z][a-z0-9_-]{0,63}$/',
            ],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', Rule::in(BootstrapTenantDefaultRoles::PERMISSIONS)],
        ];
    }
}
