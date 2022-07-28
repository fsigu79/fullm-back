<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anticipo extends Model
{
    protected $table = 'anticipos';

    protected $fillable = [
        'documento',
        'numero',
        'proveedor_id',
        'cuenta_id',
        'banco_id',
        'fecha',
        'observacion',
        'forma_pago',
        'cheque',
        'nota_contable',
        'subtotal',
        'iva',
        'iva_porcentaje',
        'total',
        'saldo',
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

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class,'cuenta_id','id');
    }

    public function banco()
    {
        return $this->belongsTo(Banco::class,'banco_id','id');
    }

}
