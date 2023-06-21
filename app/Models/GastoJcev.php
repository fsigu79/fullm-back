<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GastoJcev extends Model
{
    protected $table = 'gastos_jcev';

    protected $fillable = [
        'id',
        'documento',
        'numero',
        'cliente_id',
        'fecha',
        'observacion',
        'tipo',
        'total',
        'esactivo',
        'usuario_id',
        'usuario_update_id',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];



}
