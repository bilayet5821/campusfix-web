<?php

namespace App\Http\Requests\Student;

use App\Enums\IssuePriority;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class StoreIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStudent() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim((string) $this->input('title')),
            'description' => trim((string) $this->input('description')),
            'priority' => $this->filled('priority') ? trim((string) $this->input('priority')) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:120'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'location_id' => [
                'required',
                'integer',
                Rule::exists('locations', 'id')->where('is_active', true),
            ],
            'description' => ['required', 'string', 'min:20', 'max:1500'],
            'priority' => ['nullable', 'string', Rule::in(IssuePriority::values())],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => [
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:5120',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! $value instanceof UploadedFile) {
                        return;
                    }

                    $path = $value->getRealPath();
                    $info = $path ? @getimagesize($path) : false;
                    $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];

                    if ($info === false || ! in_array($info[2] ?? null, $allowedTypes, true)) {
                        $fail('Each attachment must be a genuine JPG, PNG, or WebP image file.');
                    }
                },
            ],
            'accuracy_confirmed' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Please choose an issue category.',
            'category_id.exists' => 'The selected category is not available.',
            'location_id.required' => 'Please choose a campus location.',
            'location_id.exists' => 'The selected campus location is not available.',
            'attachments.max' => 'You may upload at most 5 supporting images.',
            'attachments.*.image' => 'Each attachment must be a valid image file.',
            'attachments.*.mimes' => 'Only JPG, PNG, and WebP images are allowed.',
            'attachments.*.mimetypes' => 'Only JPG, PNG, and WebP images are allowed.',
            'attachments.*.max' => 'Each image must be 5 MB or smaller.',
            'accuracy_confirmed.accepted' => 'Please confirm that your issue report is accurate before submitting.',
        ];
    }
}
