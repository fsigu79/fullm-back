<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SriTabla extends Model
{
    protected $table = 'sritabla';

    protected $fillable = [
        'tabla',
        'codigo',
        'concepto',
        'valor',
        'desde',
        'hasta',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];
}
