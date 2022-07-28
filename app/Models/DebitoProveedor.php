<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebitoProveedor extends Model
{
     protected $table = 'compras';

    protected $fillable = [
        'documento',
        'numero',
        'proveedor_id',
        'fecha',
        'observacion',
        'referencia',
        'tipo_pago',
        'nota_contable',
        'subtotal',
        'subiva',
        'subcero',
        'total',
        'saldo',
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


}
