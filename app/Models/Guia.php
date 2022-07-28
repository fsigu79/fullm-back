<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guia extends Model
{
    protected $table = 'guias';
    protected $fillable = [
        'documento',
        'estado',
        'fecha_ef',
        'fecha_ei',
        'fecha_el',
        'fecha_er',
        'fecha_es',
        'fecha_dl',
        'fecha_di',
        'fecha_df',
        'fecha_ds',        
        'producto_id',
        'vehiculo_id',
        'destino_id',
        'chofer_id',
    ];
    
    //relacion muchos a 1
    public function user(){
        return $this ->belongsTo('App\Models\User','user_id');    
    }
    public function producto(){
        return $this ->belongsTo('App\Models\Producto','producto_id');    
    }
    public function vehiculo(){
        return $this ->belongsTo('App\Models\Vehiculo','vehiculo_id');    
    }
    public function destino(){
        return $this ->belongsTo('App\Models\Destino','destino_id');    
    }
    public function chofer(){
        return $this ->belongsTo('App\Models\Chofer','chofer_id');    
    }
 
    
    use HasFactory;
}
