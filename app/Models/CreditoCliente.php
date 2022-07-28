<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditoCliente extends Model
{
     protected $table = 'creditosc';

    protected $fillable = [
        'documento',
        'numero',
        'venta_id',
        'documento_cliente',
        'cliente_id',
        'autorizacion',
        'fecha',
        'fecha_caduca',
        'tipo_credito',
        'nota_contable',
        'subtotal',
        'subiva',
        'subcero',
        'iva',
        'iva_porcentaje',
        'descuento',
        'total',
        'saldo',
        'banco_id',
        'cheque',
        'descripcion',
        'esactivo',
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

    public function creditoDetalle()
    {
        return $this->hasMany(CreditoClienteDetalle::class,'venta_id','id');

    }
}
