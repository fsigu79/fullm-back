<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioTransitoDetalle extends Model
{

    protected $table = 'inventario_transitod';

    protected $fillable = [
        'id',
        'documento',
        'numero',
        'inventario_transito_id',
        'producto_id',
        'producto_codigo',
        'producto_nombre',
        'cantidad',
        'observaciond',
        'referenciad',
        'tieneiva',
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

    public function transito()
    {
        return $this->belongsTo(InventarioTransito::class,'inventario_transito_id','id');
    }


}
