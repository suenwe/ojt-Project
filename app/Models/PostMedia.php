<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostMedia extends Model
{
    protected $table = 'post_media';
    protected $primaryKey = 'media_id';

    protected $fillable = [
        'post_id',
        'media_type',
        'mime_type',
        'original_filename',
        'media_data',
        'media_path',
        'file_size',
    ];

    protected $hidden = ['media_data'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(
            Post::class,
            'post_id',
            'post_id'
        );
    }
}
