<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioTransito extends Model
{
    protected $table = 'inventario_transito';

    protected $fillable = [
        'id',
        'documento',
        'numero',
        'fecha',
        'nombre',
        'observacion',
        'subtotal',
        'subcero',
        'subiva',
        'total',
        'esactivo',
        'usuario_id',
        'usuario_login',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];




    public function TransitoDetalle()
    {
        return $this->hasMany(InventarioTransitoDetalle::class,'inventario_transito_id','id');

    }


}
