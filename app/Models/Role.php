<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

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
