<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuiaRemision extends Model
{
   protected $table = 'guias_remision';

    protected $fillable = [
        'documento',
        'serie',
        'numero',
        'factura_id',
        'factura_cliente',
        'cliente_id',
        'partida',
        'fecha_inicio',
        'fecha_fin',
        'direccion',
        'ruta',
        'motivo',
        'documento_aduanero',
        'placa',
        'transportista_id',
        'observacion',
        'autorizacion',
        'esactivo',
        'usuario_id',
        'xml',
        'fecha_autorizacion',
        'email',
        'status',
        'status_code',
        'message_error',
        'aditional_message_error',
        'ruc',
        'cliente',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];


    public function customer()
    {
        return $this->belongsTo(Customer::class,'cliente_id','id');
    }


    public function detalle()
    {
        return $this->hasMany(GuiaRemisionDetalle::class,'guiar_id','id');

    }

    public function transportista()
    {
        return $this->belongsTo(Transportista::class,'transportista_id','id');
    }
}
