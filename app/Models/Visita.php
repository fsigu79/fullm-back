<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visita extends Model
{
    protected $table = 'visitas';

    protected $fillable = [
        'documento',
        'numero',
        'cliente_id_pac',
        'cliente',
        'direccion_id',
        'fecha',
        'tipo_id',
        'contacto',
        'observaciones',
        'longitud',
        'latitud',
        'imagen1',
        'imagen2',
        'imagen3',
        'firma',
        'revision_stock',
        'ex_preferencial',
        'material_pop',
        'limpieza_producto',
        'revision_antiguedad',
        'esactivo',
        'usuario_created',
        'usuario_updated',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

     public function tipovisita()
    {
        return $this->belongsTo(TipoVisita::class,'tipo_id','id');
    }

    public function direciones()
    {
        return $this->belongsTo(Direccion::class,'direccion_id','id');
    }


}
