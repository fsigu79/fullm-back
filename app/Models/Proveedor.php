<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedores';

    protected $fillable = [
        'codigo',
        'ruc',
        'nombres',
        'apellidos',
        'direccion',
        'pais_id',
        'telefono',
        'celular',
        'contacto',
        'email',
        'tipo',
        'tipo_compra',
        'tipo_documento',
        'retencion',
        'autorizacion',
        'fecha_caduca',
        'saldo',
        'cuenta_id',
        'esrelacionado',
        'tiporuc',
        'esparaiso',
        'esactivo',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];


    public function account()
    {
        return $this->belongsTo(Cuenta::class,'cuenta_id','id');
    }
}
