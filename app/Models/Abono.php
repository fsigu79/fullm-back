<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abono extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'documento',
        'numero',
        'serie',
        'cliente_id',
        'fecha',
        'fecha_pago',
        'observacion',
        'tipo_pago',
        'letra',
        'cobrador_id',
        'nota_contable',
        'subtotal',
        'subiva',
        'subcero',
        'iva',
        'iva_porcentaje',
        'total',
        'saldo',
        'interes',
        'banco_id',
        'numero_cheque',
        'fecha_deposito',
        'esdepositado',
        'esactivo',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];


    public function customer()
    {
        return $this->belongsTo(Customer::class,'cliente_id','id');
    }


    public function rep()
    {
        return $this->belongsTo(User::class);
    }

    public function abonoDetale()
    {
        return $this->hasMany(AbonoDetalle::class,'abono_id','id');

    }
}
