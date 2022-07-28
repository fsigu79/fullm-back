<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asiento extends Model
{
     protected $table = 'asientos';

    protected $fillable = [
        'documento',
        'numero',
        'beneficiario',
        'fecha',
        'descripcion',
        'referencia',
        'modulo',
        'usuario_id',
        'esactivo',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];



    public function asientoDetalle()
    {
        return $this->hasMany(AsientoDetalle::class,'asiento_id','id');

    }
}
