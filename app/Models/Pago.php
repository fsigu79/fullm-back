<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'documento',
        'numero',
        'proveedor_id',
        'fecha',
        'observacion',
        'tipo_pago',
        'nota_contable',
        'total',
        'banco_id',
        'cheque',
        'esactivo',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];


    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class,'proveedor_id','id');
    }

    public function pagoDetalle()
    {
        return $this->hasMany(PagoDetalle::class,'pago_id','id');

    }
}
