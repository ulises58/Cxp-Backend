<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Tenant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantGroupRequest extends FormRequest
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
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('groups', 'name')->where(
                    'tenant_id',
                    (string) $this->user()->tenant_id,
                ),
            ],
            'description' => ['nullable', 'string', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
