<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transportista extends Model
{
    protected $table = 'transportistas';

    protected $fillable = [
        'nombres',
        'placa',
        'ruc',
        'tiporuc',
        'esrise',
        'llevaconta',
        'contibuyente_esp',
        'esactivo',
        //'user_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'esrise' => 'integer',
        'llevaconta' => 'integer',
        'contibuyente_esp' => 'integer',
        'esactivo' => 'integer',
    ];



}
