<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrderExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Order::with(['agency', 'cold_room', 'client', 'provider', 'user'])->get();
    }

    public function headings(): array
    {
        return [
            'estado', 
            'fecha', 
            'proveedor', 
            'cliente',
            'marcado',
            'agencia',
            'cuarto frío',
            'fecha de entrega',
            'fecha de vuelo',
            'awb',
            'usuario',
            'orden woo',
            'orden unosoft',
        ];
    }

    public function map($order) : array {
        $status_map = (object) ([
            "R" => 'Registrado', 
            "C" => 'Confimado',
            "F" => 'Facturado',
            "D" => 'Despachado',
            "O" => 'Orden Fija',
        ]);

        return [
            $status_map->{$order->status},
            $order->date,
            $order->provider->name,
            $order->client->name,
            $order->marking,
            $order->agency->name,
            $order->cold_room->name,
            $order->delivery_date,
            $order->flight_date,
            $order->awb,
            $order->user->name . ' ' . $order->user->surname,
            $order->woo_order,
            $order->unosof_order,
        ];
    }
}
