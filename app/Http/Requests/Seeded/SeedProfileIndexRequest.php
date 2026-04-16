<?php

declare(strict_types=1);

namespace App\Http\Requests\Seeded;

use Illuminate\Foundation\Http\FormRequest;

class SeedProfileIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city_id' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
