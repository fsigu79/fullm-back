<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transportista extends Model
{
    protected $table = 'transportistas';

    protected $fillable = [
        'razon_social',
        'chofer',
        'placa',
        'ruc',
        'esactivo',
        'user_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];



}
