<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReporteTransito extends Model
{
    protected $table = 'reporte_reposicion';

    protected $fillable = [
        'codigo',
        'articulo',
        'marca',
        'categoria',
        'stock',
        'mes3',
        'mes2',
        'mes1',
        'total',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];


}
