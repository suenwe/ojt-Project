<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShowNotification extends Model
{
    protected $table = 'show_notification';
    protected $primaryKey = 'sh_not_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'notification_id',
        'notification_type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(
            Notification::class,
            'notification_id',
            'notification_id'
        );
    }
}