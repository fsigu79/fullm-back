<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderBox extends Model
{
    protected $table = 'order_boxes';

    protected $fillable = [
        'id',
        'order_id',
        'box_id',
        'box_number',
    ];

    protected $hidden = [
        'created_at', 
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function box()
    {
        return $this->belongsTo(Box::class);
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
