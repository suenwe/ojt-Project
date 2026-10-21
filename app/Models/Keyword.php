<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    protected $table = 'keywords';

    protected $primaryKey = 'keyword_id';

    public $timestamps = false;

    protected $fillable = ['keyword_text'];

    /**
     * Find the first restricted keyword that appears anywhere in the text (ignoring upper/lower case), if any.
     */
    public static function firstFoundIn(string $text): ?string
    {
        return static::query()
            ->orderBy('keyword_id')
            ->pluck('keyword_text')
            ->first(fn (string $keyword): bool => $keyword !== '' && mb_stripos($text, $keyword) !== false);
    }
}
