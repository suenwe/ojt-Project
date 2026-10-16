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
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id', 'user_id');
    }
}