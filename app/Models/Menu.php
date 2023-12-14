<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';
    protected $fillable = [
        'id',
        'code',
        'module',
        'description',
        'name',
        'url',
        'icon',
        'created_at',
        'updated_att',
        'parent_id',
        'isactive',
        'app'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'parent_id' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id', 'id');
    }
    public function items()
    {
        return $this->hasMany(Menu::class, 'parent_id', 'id')->where("isactive", 1);
    }
    public function permission()
    {
        return $this->hasMany(Access::class, 'menu_id', 'id');
    }
}
