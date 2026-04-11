<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Tenant;

use App\Models\Group;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tenant_id !== null;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        /** @var Group $group */
        $group = $this->route('tenantGroup');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('groups', 'name')
                    ->where('tenant_id', (string) $this->user()->tenant_id)
                    ->ignore($group->getKey()),
            ],
            'description' => ['sometimes', 'nullable', 'string', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
