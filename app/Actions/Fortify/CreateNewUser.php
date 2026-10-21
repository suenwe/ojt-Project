<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Rules\UniqueUsername;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    public function create(array $input): User
    {
        Validator::make($input, [
            'username' => ['required', 'string', 'min:3', 'max:30', new UniqueUsername],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'birthday' => ['required', 'date', 'before_or_equal:today'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->numbers()->symbols(),
            ],
        ])->validate();

        return User::create([
            'username' => $input['username'],
            'email' => $input['email'],
            'phone_number' => $input['phone_number'] ?? null,
            'birthday' => $input['birthday'],
            'password' => $input['password'],
            'role' => 'user',
        ]);
    }
}
