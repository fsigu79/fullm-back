<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobus extends Model
{
    protected $table = 'cobus';

    protected $fillable = [
        'codigo',
        'razon_social',
        'ruc',
        'arancel',
        'despacho',
        'marca',
        'modelo',
        'referendo',
        'fecha_liquidacion',
        'fecha_salida',
        'pais',
        'fob',
        'flete',
        'seguro',
        'cif',
        'base_imponible',
        'unidades',
        'precio',
        'adval',
        'banco_id',
        'embarcador',
        'total_fob',
        'total_flete',
        'total_seguro',
        'total_cif',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

}
