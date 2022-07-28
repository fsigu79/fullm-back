<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kardex extends Model
{
     protected $table = 'kardex';

    protected $fillable = [
        'fecha',
        'documento',
        'referencia',
        'origen',
        'ingresos',
        'egresos',
        'saldo',
        'precio',
        'costo',
        'total',
        'observacion',
    ];


}
