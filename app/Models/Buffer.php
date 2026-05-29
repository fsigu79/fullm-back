<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buffer extends Model
{
    protected $table = 'buffers';

    protected $fillable = [
        'anio',
        'cod_cliente',
        'direccion_id',
        'marca_id',
        'marca',
        'codigo',
        'descripcion',
        'precio',
        'enero',
        'febrero',
        'marzo',
        'abril',
        'mayo',
        'junio',
        'julio',
        'agosto',
        'septiembre',
        'octubre',
        'noviembre',
        'diciembre',
        'total',
        'total_usd',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];


}


