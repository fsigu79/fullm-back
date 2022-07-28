<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditoProveedor extends Model
{
    protected $table = 'creditosp';

    protected $fillable = [
        'documento',
        'numero',
        'documento_proveedor',
        'fecha',
        'fecha_caduca',
        'autorizacion',
        'proveedor_id',
        'fecha',
        'fecha_pago',
        'observacion',
        'tipo_pago',
        'nota_contable',
        'subtotal',
        'subiva',
        'subcero',
        'iva',
        'iva_porcentaje',
        'descuento',
        'total',
        'ice',
        'retencion',
        'saldo',
        'metodopago_id',
        'orden_id',
        'cuenta_gasto_id',
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


    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class,'proveedor_id','id');
    }

    public function creditoDetalle()
    {
        return $this->hasMany(CreditoProveedorDetalle::class,'compra_id','id');

    }


}
