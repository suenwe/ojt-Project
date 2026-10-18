<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable([
    'username',
    'photo',
    'email',
    'password',
    'phone_number',
    'role',
    'two_factor_code',
    'two_factor_expires_at',
    'website',
    'ip_address',
    'browser',
    'banned_at',
    'ban_reason',
    'birthday',
])]

#[Hidden(['password', 'remember_token', 'two_factor_code'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'user_id';

    public const CREATED_AT = 'created_date';

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'created_date' => 'datetime',
            'password' => 'hashed',
            'two_factor_expires_at' => 'datetime',
            'banned_at' => 'datetime',
            'birthday' => 'date',
        ];
    }

    /**
     * Get the profile photo as a data URI, or null when the member has no usable photo.
     */
    public function photoDataUri(): ?string
    {
        $photo = $this->photo;

        if (is_resource($photo)) {
            $photo = stream_get_contents($photo, -1, 0);
        }

        if (! is_string($photo) || $photo === '') {
            return null;
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($photo);

        if (! in_array($mimeType, ['image/jpeg', 'image/png'], true)) {
            return null;
        }

        return 'data:'.$mimeType.';base64,'.base64_encode($photo);
    }

    /**
     * Get up to two uppercase letters of the username for the avatar fallback.
     */
    public function initials(): string
    {
        return Str::upper(Str::substr($this->username, 0, 2));
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'user_id', 'user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'user_id', 'user_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class, 'user_id', 'user_id');
    }

    public function postViews(): HasMany
    {
        return $this->hasMany(PostView::class, 'user_id', 'user_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id', 'user_id');
    }

    public function receivedNotifications(): HasMany
    {
        return $this->hasMany(ShowNotification::class, 'user_id', 'user_id');
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(UserAccessLog::class, 'user_id', 'user_id');
    }
}
