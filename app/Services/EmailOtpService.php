<?php

namespace App\Services;

use App\Notifications\SendTwoFactorCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

class EmailOtpService
{
    private const SESSION_KEY = 'email_otp_challenge';

    /**
     * @param  array<string, mixed>  $data
     */
    public function issue(
        string $email,
        string $purpose,
        ?int $userId = null,
        array $data = [],
        bool $deliver = true,
    ): void {
        $code = $deliver
            ? str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT)
            : null;

        session()->put(self::SESSION_KEY, [
            'email' => $email,
            'purpose' => $purpose,
            'user_id' => $userId,
            'code_hash' => $code === null ? null : Hash::make($code),
            'expires_at' => now()->addMinute()->toISOString(),
            'data' => $data,
        ]);

        if ($code !== null) {
            Notification::route('mail', $email)->notify(new SendTwoFactorCode($code, $purpose));
        }
    }

    /**
     * @return array{email: string, purpose: string, user_id: int|null, code_hash: string|null, expires_at: string, data: array<string, mixed>}|null
     */
    public function current(): ?array
    {
        $challenge = session()->get(self::SESSION_KEY);

        if (! is_array($challenge)
            || ! is_string($challenge['email'] ?? null)
            || ! is_string($challenge['purpose'] ?? null)
            || ! (is_int($challenge['user_id'] ?? null) || is_null($challenge['user_id'] ?? null))
            || ! (is_string($challenge['code_hash'] ?? null) || is_null($challenge['code_hash'] ?? null))
            || ! is_string($challenge['expires_at'] ?? null)
            || ! is_array($challenge['data'] ?? null)) {
            return null;
        }

        return $challenge;
    }

    public function hasExpired(): bool
    {
        $challenge = $this->current();

        return $challenge === null || now()->greaterThanOrEqualTo($challenge['expires_at']);
    }

    public function verify(string $code): bool
    {
        $challenge = $this->current();

        if ($challenge === null || $this->hasExpired() || $challenge['code_hash'] === null) {
            return false;
        }

        return Hash::check($code, $challenge['code_hash']);
    }

    public function invalidateCode(): void
    {
        $challenge = $this->current();

        if ($challenge !== null) {
            $challenge['code_hash'] = null;
            session()->put(self::SESSION_KEY, $challenge);
        }
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
