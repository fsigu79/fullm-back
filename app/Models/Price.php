<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
   protected $table = 'precios';

    protected $fillable = [
        'id',
        'nombre',
        'tieneiva',
        'porcentaje',
        'esactivo'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];


}
