<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos';

    protected $fillable = [
        'documento',
        'numero',
        'fecha',
        'nota_contable',
        'precio_id',
        'destino_id',
        'referencia',
        'observacion',
        'subtotal',
        'subiva',
        'subcero',
        'total',
        'esactivo',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];


    public function destino()
    {
        return $this->belongsTo(Destino::class,'destino_id','id');
    }

    public function movimientoDetalle()
    {
        return $this->hasMany(MovimientoInventarioDetalle::class,'movimiento_id','id');

    }


}
