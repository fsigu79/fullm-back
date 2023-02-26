<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuertoEmbarque extends Model
{
    protected $table = 'puertos_embarque';

    protected $fillable = [
        'nombre',
        'dias_entrega',
        'esactivo',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];



}
