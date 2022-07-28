<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Cuenta extends Model
{
    protected $table = 'cuentas';

     protected $fillable = [
        'codigo',
        'nombre',
        'codigo_padre',
        'tipo',
        'nivel',
        'estransaccional',
        'orden',
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
