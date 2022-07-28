<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Observacion extends Model
{
    protected $table = 'observaciones';
    protected $fillable = [
        'contenido',
        'guia_id',
        'tipo_id',
    ];
    
    //relacion muchos a 1
    public function user(){
        return $this ->belongsTo('App\Models\User','user_id');    
    }

    public function guia(){
        return $this ->belongsTo('App\Models\Guia','guia_id');    
    }
 
    public function tipo(){
        return $this ->belongsTo('App\Models\Tipo','tipo_id');    
    }
    
    use HasFactory;
}
