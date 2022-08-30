<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventarioDetalle extends Model
{

    protected $table = 'movimientosd';

    protected $fillable = [
        'id',
        'movimiento_id',
        'documento',
        'numero',
        'producto_id',
        'producto_codigo',
        'producto_nombre',
        'cantidad',
        'costo',
        'recibidos',
        'observaciond',
        'referenciad',
        'tieneiva',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class,'producto_id','id');
    }

    public function movimiento()
    {
        return $this->belongsTo(Movimiento::class,'movimiento_id','id');
    }


}
