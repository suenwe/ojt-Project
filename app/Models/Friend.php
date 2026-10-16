<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    protected $table = 'friends';
    protected $primaryKey = 'first_user_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'first_user_id',
        'second_user_id',
        'state',
    ];
}