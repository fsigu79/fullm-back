<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_detail';

    protected $fillable = [
        'id',
        'order_box_id',
        'product_id',
        'longitude',
        'observation',
        'price',
        'stems',
        'total'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function orderBox()
    {
        return $this->belongsTo(OrderBox::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function Order()
    {
        return $this->belongsTo(Order::class);
    }
}
