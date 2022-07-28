<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsientoDetalle extends Model
{
    protected $table = 'asientosd';

    protected $fillable = [
        'asiento_id',
        'documento',
        'numero',
        'cuenta_id',
        'observacion',
        'debe',
        'haber',
        'fecha_conciliacion',
        'esconciliado',
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

    public function asiento()
    {
        return $this->belongsTo(Asiento::class,'asiento_id','id');
    }
}
