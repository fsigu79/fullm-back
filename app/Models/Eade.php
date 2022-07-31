<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eade extends Model
{
    protected $table = 'eade';

    protected $fillable = [
        'codigo',
        'mes',
        'anio_modelo',
        'marca',
        'modelo',
        'segmento',
        'subsegmento',
        'gama',
        'pais',
        'provincia',
        'canton',
        'cilindraje',
        'combustible',
        'avaluo',
        'unidades',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

}
