<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class UniqueUsername implements ValidationRule
{
    /**
     * @param  int|null  $ignoreUserId  The member whose own username should not count as taken.
     */
    public function __construct(private ?int $ignoreUserId = null) {}

    /**
     * Fail when another member already uses this username, ignoring upper and lower case.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || self::isTaken($value, $this->ignoreUserId)) {
            $fail('This username is already taken. Please choose another one.');
        }
    }

    /**
     * Check whether a username is already used by another member, ignoring upper and lower case.
     */
    public static function isTaken(string $username, ?int $ignoreUserId = null): bool
    {
        return User::query()
            ->whereRaw('LOWER(username) = ?', [mb_strtolower(trim($username))])
            ->when($ignoreUserId, fn ($query) => $query->where('user_id', '!=', $ignoreUserId))
            ->exists();
    }
}
