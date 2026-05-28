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
        'cliente_id',
        'factura_id',
        'factura_cliente',
        'partida',
        'fecha_inicio',
        'fecha_fin',
        'direccion',
        'ruta',
        'motivo',
        'placa',
        'transportista_id',
        'observacion',
        'esactivo',
        'origen',
        'ruc',
        'cliente',
        'direccion_id',
        'ruc_transportista',
        'nombre_transportista',
        'documentos',
        'empresa',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function detalle()
    {
        return $this->hasMany(GuiaRemisionDetalleDwh::class, 'guiar_id', 'id');
    }
}
