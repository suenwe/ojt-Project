<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';
    public $timestamps = false;

    protected $fillable = [
        'notification_type',
        'notification_text',
    ];

    public function recipients(): HasMany
    {
        return $this->hasMany(
            ShowNotification::class,
            'notification_id',
            'notification_id'
        );
    }
}