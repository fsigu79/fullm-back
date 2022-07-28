<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'id',
        'status',
        'date',
        'provider_id',
        'client_id',
        'marking',
        'agency_id',
        'cold_room_id',
        'delivery_date',
        'flight_date',
        'awb',
        'user_id',
        'woo_order',
        'unosof_order',
        'observation',
        'total_stems',
        'total'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function cold_room()
    {
        return $this->belongsTo(ColdRoom::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderBoxes()
    {
        return $this->hasMany(OrderBox::class);
    }
}
