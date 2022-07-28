<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeriePac extends Model
{
    protected $table = 'maeser';

    protected $fillable = [
        'modelo',
        'motor',
        'chasis',
        'anio',
        'color',
        'cpn',
        'ramv',
        'cvanulada04',
        'codprod04',
    ];

    protected $hidden = [

    ];

    protected $casts = [

    ];



}
