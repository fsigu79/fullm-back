<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoDetalle extends Model
{
    protected $table = 'pagosd';

    protected $fillable = [
        'pago_id',
        'documento',
        'numero',
        'compra_id',
        'descripcion',
        'valor',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class,'compra_id','id');
    }

    public function pago()
    {
        return $this->belongsTo(Pago::class,'pago_id','id');
    }
}
