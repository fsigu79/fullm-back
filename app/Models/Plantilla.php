<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plantilla extends Model
{
    protected $table = 'plantillas';

    protected $fillable = [
        'documento',
        'modulo',
        'descripcion',
        'nombre',
        'cuenta_id',
        'tipo',
        'esactivo',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];



    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class,'cuenta_id','id');
    }



}
