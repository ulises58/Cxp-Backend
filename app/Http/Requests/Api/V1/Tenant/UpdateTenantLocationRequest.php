<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Tenant;

use App\Models\Location;
use App\Models\Site;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantLocationRequest extends FormRequest
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
        /** @var Site $site */
        $site = $this->route('tenantSite');
        /** @var Location $location */
        $location = $this->route('tenantLocation');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('locations', 'slug')
                    ->where(static fn ($q) => $q->where('site_id', $site->id))
                    ->ignore($location->id),
            ],
            'description' => ['sometimes', 'nullable', 'string', 'max:65535'],
            'metadata' => ['sometimes', 'nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
