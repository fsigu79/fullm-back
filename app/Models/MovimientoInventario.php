<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos';

    protected $fillable = [
        'id',
        'documento',
        'numero',
        'cliente_id',
        'cliente_nombre',
        'fecha',
        'nota_contable',
        'precio_id',
        'referencia',
        'observacion',
        'subtotal',
        'subiva',
        'subcero',
        'iva',
        'total',
        'esactivo',
        'usuario_id',

    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];



    public function movimientoDetalle()
    {
        return $this->hasMany(MovimientoInventarioDetalle::class,'movimiento_id','id');

    }


}
