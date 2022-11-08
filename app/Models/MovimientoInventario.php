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
        'serie',
        'numero',
        'nota_contable',
        'fecha',
        'destino_id',
        'cliente_id',
        'cliente_nombre',
        'cliente_codigo',
        'cliente_ruc',
        'precio_id',
        'referencia',
        'observacion',
        'subtotal',
        'subcero',
        'subiva',
        'iva',
        'total',
        'aprobado',
        'registrado',
        'referencia_pac',
        'descripcion',
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


    public function destino()
    {
        return $this->belongsTo(Destino::class,'destino_id','id');
    }


    public function movimientoDetalle()
    {
        return $this->hasMany(MovimientoInventarioDetalle::class,'movimiento_id','id');

    }


}
