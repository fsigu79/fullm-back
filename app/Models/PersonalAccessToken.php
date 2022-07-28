<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalAccessToken extends Model
{
    protected $table = 'personal_access_tokens';

    protected $fillable = [
        'id',
        'tokenable_type',
        'tokenable_id',
        'name',
        'token',
        'abilites',
    ];

    protected $hidden = [
        'created_at', 
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];
}
