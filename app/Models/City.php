<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'ciudades';

    protected $fillable = [
        'nombre',
        'provincia_id',
        'esactivo',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function Provincia()
    {
        return $this->belongsTo(Provincia::class,'provincia_id','id');
    }



}
