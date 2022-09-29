<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class Invoice extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'documento',
        'serie',
        'numero',
        'cliente_id',
        'fecha',
        'fecha_pago',
        'precio_id',
        'referencia',
        'vendedor_id',
        'numero_asiento',
        'subtotal',
        'subiva',
        'subcero',
        'iva',
        'iva_porcentaje',
        'descuento',
        'total',
        'costo',
        'saldo',
        'escontado',
        'metodopago_id',
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

    public function price()
    {
        return $this->belongsTo(Price::class);
    }

    public function payment()
    {
        return $this->belongsTo(PaymentMethod::class);
    }


    public function rep()
    {
        return $this->belongsTo(User::class);
    }

    public function invoiceDetail()
    {
        return $this->hasMany(InvoiceDetail::class,'venta_id','id');

    }
}
