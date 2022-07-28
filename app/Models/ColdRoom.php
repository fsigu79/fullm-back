<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColdRoom extends Model
{
    protected $table = 'cold_rooms';

    protected $fillable = [
        'id',
        'name',
    ];

    protected $hidden = [
        'created_at', 
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];
}
