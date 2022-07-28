<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditoClienteDetalle extends Model
{
    protected $table = 'creditoscd';

    protected $fillable = [
        'credito_id',
        'documento',
        'numero',
        'producto_id',
        'descripcion',
        'cantidad',
        'tieneiva',
        'descuento',
        'descuentop',
        'costo',
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

    public function credito()
    {
        return $this->belongsTo(CreditoCliente::class,'venta_id','id');
    }
}
