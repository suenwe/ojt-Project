<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Rules\UniqueUsername;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use PDO;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function update(User $user, array $input): void
    {
        $rules = [
            'username' => ['required', 'string', 'min:3', 'max:30', new UniqueUsername($user->user_id)],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user),
            ],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'url', 'max:255'],
            'birthday' => ['required', 'date', 'before_or_equal:today'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ];

        if (mb_strtolower(trim((string) ($input['email'] ?? ''))) !== mb_strtolower($user->email)) {
            $rules['current_password'] = ['required', 'string', 'current_password:web'];
        }

        $validated = Validator::make($input, $rules, [
            'current_password.required' => 'Enter your current password to change your email address.',
            'current_password.current_password' => 'The current password is incorrect.',
        ])->validateWithBag('updateProfileInformation');

        $user->forceFill([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'website' => $validated['website'] ?? null,
            'birthday' => $validated['birthday'],
        ])->save();

        if (($validated['photo'] ?? null) instanceof UploadedFile) {
            $this->savePhoto($user, $validated['photo']);
        } elseif ($validated['remove_photo'] ?? false) {
            $user->forceFill(['photo' => null])->save();
        }
    }

    /**
     * Store the uploaded photo in the binary "photo" column.
     *
     * The bytes are bound as a LOB because PostgreSQL rejects raw image bytes sent as text.
     */
    protected function savePhoto(User $user, UploadedFile $photo): void
    {
        $photoBytes = $photo->getContent();

        $statement = $user->getConnection()->getPdo()->prepare(
            'update users set photo = ? where user_id = ?'
        );

        $statement->bindValue(1, $photoBytes, PDO::PARAM_LOB);
        $statement->bindValue(2, $user->getKey(), PDO::PARAM_INT);
        $statement->execute();

        $user->setRawAttributes(['photo' => $photoBytes] + $user->getAttributes(), true);
    }
}
