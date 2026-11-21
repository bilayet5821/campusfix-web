<?php

namespace App\Http\Requests\Admin;

use App\Models\Building;
use App\Models\Location;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'room_or_area' => trim((string) $this->input('room_or_area')),
            'floor' => $this->filled('floor') ? trim((string) $this->input('floor')) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'building_id' => [
                'required',
                'integer',
                Rule::exists('buildings', 'id'),
            ],
            'room_or_area' => [
                'required',
                'string',
                'min:1',
                'max:100',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! $this->filled('building_id') || ! ctype_digit((string) $this->input('building_id'))) {
                        return;
                    }

                    $buildingId = (int) $this->input('building_id');
                    $room = strtolower((string) $value);

                    $duplicateRoom = Location::where('building_id', $buildingId)
                        ->whereRaw('LOWER(room_or_area) = ?', [$room])
                        ->exists();

                    if ($duplicateRoom) {
                        $fail('This room or area already exists in the selected building.');

                        return;
                    }

                    $building = Building::find($buildingId);
                    if ($building) {
                        $displayName = strtolower("{$building->name} — {$value}");
                        if (Location::whereRaw('LOWER(name) = ?', [$displayName])->exists()) {
                            $fail('This building and room combination already exists.');
                        }
                    }
                },
            ],
            'floor' => ['nullable', 'string', 'max:40'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'building_id.required' => 'Please select a building for this room.',
            'building_id.exists' => 'The selected building does not exist.',
            'room_or_area.required' => 'Please enter a room number or area name.',
        ];
    }
}
