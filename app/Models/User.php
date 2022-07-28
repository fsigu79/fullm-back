<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    protected $fillable = [
        'name',
        'surname',
        'login',
        'role',
        'description',
        'email',
        'password',
        'profile_id',
        'image',
        'issalesrep',
        'iscollector',
        'iscash',
        'isactive',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
          'id' => 'integer',
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['full_name'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    public function getJWTCustomClaims()
    {
        return [
            'email' => $this->email,
            'full_name' => $this->full_name,
            'profile' => $this->profile->id,
            'isactive' => $this->isactive,
            'auid' => $this->auid,
        ];
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }


    public function getFullNameAttribute()
    {
        return "{$this->name} {$this->surname}";
    }
}
