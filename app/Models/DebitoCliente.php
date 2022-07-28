<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebitoCliente extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'id',
        'documento',
        'serie',
        'numero',
        'cliente_id',
        'fecha',
        'fecha_pago',
        'referencia', 
        'vendedor_id',
        'nota_contable', 
        'subtotal',
        'subcero',
        'iva',
        'iva_porcentaje' ,
        'total', 
        'saldo', 
        'esactivo', 
        'observacion', 
        'usuario_id',
        'created_at',
        'updated_at'

    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];


    public function cliente()
    {
        return $this->belongsTo(Customer::class,'cliente_id','id');
    }
}
