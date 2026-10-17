<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
    'birthday' => 'date',
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
        ];
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
