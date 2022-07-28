<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
   protected $table = 'documentos';

    protected $fillable = [
        'codigo',
        'modulo',
        'nombre',
        'numero',
        'serie',
        'signo',
        'codigo_contable',
        'esactivo'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];


}
