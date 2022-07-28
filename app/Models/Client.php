<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'id',
        'codigo',
        'ruc',
        'nombre',
        'medidor',
        'provincia',
        'canton',
        'area',
        'sector',
        'parroquia',
        'comunidad',
        'zona',
        'este',
        'norte',

    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];


}
