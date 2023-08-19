<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaRpa extends Model
{


    protected $fillable = [
        'tipodocto31',
        'nofact31',
        'nocte31',
        'nomcte31',
        'subcab',
        'estadofac',
        'subtotal',
        'iva',
        'agente_retencion',
        'descripcion',
        'bienes_iva',
        'servicios_iva',
        'bienes_renta',
        'servicios_renta',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

}
