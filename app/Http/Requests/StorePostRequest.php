<?php

namespace App\Http\Requests;

use App\Services\PostPublisher;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'content' => ['required', 'string', 'max:1200'],
            'category_id' => ['required', 'integer', 'exists:categories,category_id'],
            'post_type' => ['required', Rule::in(PostPublisher::POST_TYPES)],
            'hashtag' => ['nullable', 'string', 'max:150'],
            'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['file', 'max:25600', 'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/webm'],
        ];

        if ($this->routeIs('schedule-post.store')) {
            $rules['scheduled_at'] = ['required', 'date', 'after:now'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'scheduled_at.after' => 'Please choose a future date and time.',
            'media.*.max' => 'Each file must be 25 MB or smaller.',
            'media.*.mimetypes' => 'Only JPG, PNG, WEBP images and MP4 or WEBM videos can be uploaded.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function after(): array
    {
        return [
            function ($validator): void {
                foreach ($this->file('media') ?? [] as $index => $file) {
                    if (str_starts_with((string) $file->getMimeType(), 'image/') && $file->getSize() > 5 * 1024 * 1024) {
                        $validator->errors()->add("media.{$index}", 'Images must be 5 MB or smaller.');
                    }
                }
            },
        ];
    }
}
