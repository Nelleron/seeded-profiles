<?php

declare(strict_types=1);

namespace App\Http\Requests\Seeded;

use Illuminate\Foundation\Http\FormRequest;

class SeedProfileDestroyByCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city_id' => ['required', 'integer', 'min:1', 'exists:cities,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Поддержка обоих вариантов: из роута (web) или из query/body (API)
        $cityId = $this->route('city_id') ?? $this->input('city_id');

        $this->merge([
            'city_id' => $cityId,
        ]);
    }
}
