<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioTransito extends Model
{
    protected $table = 'inventario_transito';

    protected $fillable = [
        'id',
        'documento',
        'numero',
        'fecha',
        'nombre',
        'observacion',
        'subtotal',
        'subcero',
        'subiva',
        'total',
        'esactivo',
        'usuario_id',
        'usuario_login',
        'liquidado',

        'proveedor_codigo',
        'proveedor_nombre',
        'forwarder',
        'contenedor',
        'contenedor_numero',
        'ro',
        'flete',
        'ejecutivo_id',
        'fecha_etd',
        'fecha_eta',
        'fecha_apb',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];




    public function TransitoDetalle()
    {
        return $this->hasMany(InventarioTransitoDetalle::class,'inventario_transito_id','id');

    }


}
