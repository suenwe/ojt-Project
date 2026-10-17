<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $table = 'reports';

    protected $primaryKey = 'report_id';

    public $timestamps = false;

    protected $fillable = [
        'reporter_id',
        'target_id',
        'report_type',
        'reason',
        'status',
        'reviewed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id', 'user_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'target_id', 'post_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'user_id');
    }
}
