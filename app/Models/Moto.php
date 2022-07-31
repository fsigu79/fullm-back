<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moto extends Model
{
    protected $table = 'motos';

    protected $fillable = [
        'codigo',
        'marca_id',
        'modelo_id',
        'segmento_id',
        'codigo_pac',
        'esactivo',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function marca()
    {
        return $this->belongsTo(Marca::class,'marca_id','id');
    }
    public function modelo()
    {
        return $this->belongsTo(Modelo::class,'modelo_id','id');
    }
    public function segmento()
    {
        return $this->belongsTo(Segmento::class,'segmento_id','id');
    }

}
