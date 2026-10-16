<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    protected $table = 'keywords';
    protected $primaryKey = 'keyword_id';
    public $timestamps = false;

    protected $fillable = ['keyword_text'];
}