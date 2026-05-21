<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuiaRemisionDwh extends Model
{
    protected $connection = 'pgsqlnexus';
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
        'origen',
        'direccion_id',
        'ruc_transportista',
        'nombre_transportista',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function detalle()
    {
        return $this->hasMany(GuiaRemisionDetalleDwh::class, 'guiar_id', 'id');
    }
}
