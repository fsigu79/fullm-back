<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'codigo',
        'ruc',
        'nombre',
        'apellido',
        'razon_social',
        'direccion',
        'telefono',
        'celular',
        'contacto',
        'email',
        'vendedor_id',
        'precio_id',
        'saldo',
        'anticipo',
        'ciudad_id',
        'cupo',
        'tipo',
        'dias_credito',
        'cuenta_id',
        'tipo_empresa',
        'eslistanegra',
        'provincia_id',
        'pais_id',
        'esactivo',//test
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function Rep()
    {
        return $this->belongsTo(User::class,'vendedor_id','id');
    }

    public function account()
    {
        return $this->belongsTo(Cuenta::class,'cuenta_id','id');
    }

}
