<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    //relacion de 1 a mcuhos
    public function posts(){
        return $this ->hasMany('App\Models\Post');    
    }

use HasFactory;
}
