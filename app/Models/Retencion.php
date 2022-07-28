<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retencion extends Model
{
    protected $table = 'retenciones';

    protected $fillable = [
        'compra_id',
        'documento',
        'numero_compra',
        'tipo_compra',
        'tipo_documento',
        'factura_autorizacion',
        'factra_fecha_caduca',
        'devuelveiva',
        'numero_retencion',
        'retencion_autorizacion',
        'iva_codigo',
        'iva_procentaje',
        'iva_base',
        'iva_valor',
        'iva_codigo1',
        'iva_procentaje1',
        'iva_base1',
        'iva_valor1',
        'fuente_codigo',
        'fuenta_porcentaje',
        'fuente_base',
        'fuente_valor',
        'fuente_codigo1',
        'fuenta_porcentaje1',
        'fuente_base1',
        'fuente_valor1',
        'total',
        'informacion_pago',
        'codigo_pais',
        'forma_pago',
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

}
