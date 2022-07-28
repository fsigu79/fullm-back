<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraDetalle extends Model
{
    protected $table = 'comprasd';

    protected $fillable = [
        'compra_id',
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

    public function compra()
    {
        return $this->belongsTo(Compra::class,'compra_id','id');
    }
}
