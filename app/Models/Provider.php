<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $table = 'providers';

    protected $fillable = [
        'id',
        'name',
        'email',
        'contact',
        'address',
        'phone',
        'city_id',
    ];

    protected $hidden = [
        'created_at', 
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function city() 
    {
        return $this->belongsTo(City::class);
    }
}
