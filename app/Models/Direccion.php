<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direccion  extends Model
{
    protected $table = 'direcciones';

     protected $fillable = [
        'codigo_cliente_pac',
        'nombre_cliente_pac',
        'nombre_comercial',
        'nombre',
        'ciudad',
        'direccion',
        'telefono',
        'contacto',
        'correo',
        'esactivo',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

}
