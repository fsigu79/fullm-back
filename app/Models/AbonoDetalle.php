<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbonoDetalle extends Model
{
    protected $table = 'abonosd';

    protected $fillable = [
        'abono_id',
        'documento',
        'numero',
        'venta_id',
        'documento_cliente',
        'descripcion',
        'valor',
        'interes',
        'letra',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];


    public function invoice()
    {
        return $this->belongsTo(Invoice::class,'venta_id','id');
    }
}
