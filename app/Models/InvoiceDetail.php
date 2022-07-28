<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    protected $table = 'ventasd';

    protected $fillable = [
        'venta_id',
        'codigo',
        'documento',
        'producto_id',
        'descripcion',
        'cantidad',
        'tieneiva',
        'descuento',
        'descuentop',
        'costo',
        'precio',
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

    public function invoice()
    {
        return $this->belongsTo(Invoice::class,'venta_id','id');
    }
}
