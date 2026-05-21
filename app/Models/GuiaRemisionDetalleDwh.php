<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuiaRemisionDetalleDwh extends Model
{
    protected $connection = 'pgsqlnexus';
    protected $table = 'guias_remisiond';
    public $timestamps = false;

    protected $fillable = [
        'documento',
        'numero',
        'guiar_id',
        'producto_id',
        'descripcion',
        'cantidad',
        'codigo',
        'chasis',
        'serie',
    ];

    protected $hidden = ['created_at', 'updated_at'];
}
