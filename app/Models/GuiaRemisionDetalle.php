<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuiaRemisionDetalle extends Model
{
    protected $table = 'guias_remisiond';

    protected $fillable = [
        'documento',
        'numero',
        'guiar_id',
        'producto_id',
        'descripcion',
        'cantidad'

    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class,'producto_id','id');
    }

    public function guiar()
    {
        return $this->belongsTo(GuiaRemision::class,'guiar_id','id');
    }
}
