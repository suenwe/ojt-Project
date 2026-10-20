<?php

namespace App\Http\Requests;

use App\Models\Comment;
use App\Models\Keyword;
use App\Models\Post;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCommentRequest extends FormRequest
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
        return [
            'content' => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Write something before sending.',
            'content.max' => 'Comments can be up to 1000 characters.',
        ];
    }

    /**
     * Check restricted keywords, and that a reply answers a comment on the same post.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $validator->errors()->has('content')) {
                    $matchingKeyword = Keyword::firstFoundIn((string) $this->input('content', ''));

                    if ($matchingKeyword !== null) {
                        $validator->errors()->add(
                            'content',
                            'Your comment contains the restricted keyword "'.$matchingKeyword.'" and cannot be posted.',
                        );
                    }
                }

                if ($this->filled('parent_id') && $this->parentComment() === null) {
                    $validator->errors()->add('parent_id', 'The comment you are replying to no longer exists.');
                }
            },
        ];
    }

    /**
     * The comment being replied to, if it belongs to the same post.
     */
    public function parentComment(): ?Comment
    {
        $post = $this->route('post');

        if (! $this->filled('parent_id') || ! $post instanceof Post) {
            return null;
        }

        return Comment::query()
            ->where('post_id', $post->post_id)
            ->find($this->integer('parent_id'));
    }
}
