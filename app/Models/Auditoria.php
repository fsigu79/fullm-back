<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditorias';

    protected $fillable = [
        'id',
        'agencia_id',
        'modulo',
        'programa',
        'documento',
        'serie',
        'numero',
        'documento_completo',
        'fecha',
        'accion',
        'valor',
        'fecha_documento',
        'usuario_id',
        'usuario_login',
        'pc',
        'observacion',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];



    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

}
