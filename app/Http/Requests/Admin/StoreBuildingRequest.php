<?php

namespace App\Http\Requests\Admin;

use App\Models\Building;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreBuildingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'code' => $this->filled('code') ? strtoupper(trim((string) $this->input('code'))) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (Building::whereRaw('LOWER(name) = ?', [strtolower((string) $value)])->exists()) {
                        $fail('A building with this name already exists.');
                    }
                },
            ],
            'code' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9-]+$/',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value !== null && Building::whereRaw('LOWER(code) = ?', [strtolower((string) $value)])->exists()) {
                        $fail('A building with this code already exists.');
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a building name.',
            'code.regex' => 'Building code may only contain letters, numbers, and hyphens.',
        ];
    }
}
