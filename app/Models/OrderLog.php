<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    protected $table = 'order_logs';

    protected $fillable = [
        'id',
        'user_id',
        'order_id',
        'action',
    ];

    protected $hidden = [
        'created_at', 
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function user() 
    {
        return $this->belongsTo(User::class);
    }

    public function order() 
    {
        return $this->belongsTo(Order::class);
    }
}
