<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAccessLog extends Model
{
    protected $table = 'user_access_logs';

    protected $primaryKey = 'access_log_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ip_address',
        'browser',
        'method',
        'path',
        'route_name',
        'accessed_at',
    ];

    protected function casts(): array
    {
        return ['accessed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
