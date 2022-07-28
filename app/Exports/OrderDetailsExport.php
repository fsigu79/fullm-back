<?php

namespace App\Exports;

use App\Models\OrderDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrderDetailsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function  __construct($agency, $client, $start_at, $end_to, $role, $id, $status, $report)
    {
        $this->start_at = $start_at;
        $this->end_to = $end_to;
        $this->agency = $agency;
        $this->client = $client;
        $this->role = $role;
        $this->id = $id;
        $this->status = $status;
        $this->report = $report;
    }

    public function collection()
    {
        //var_dump($this->status);
        //DESCARGA EXCEL GENERAL
        if($this->role == 1 && $this->report == 2){
            return OrderDetail::with([
            'orderBox',
            'orderBox.box',
            'orderBox.order',
            'orderBox.order.agency',
            'orderBox.order.client',
            'orderBox.order.cold_room',
            'orderBox.order.provider',
            'orderBox.order.user',
            'product',
        ])
            ->whereHas('orderBox.order', function ($query) {
                return $query->whereBetween('flight_date', [$this->start_at, $this->end_to]);
                    //->where('status', $this->status);
            })
            ->get();
        }elseif($this->role != 1 && $this->report == 2){
            return OrderDetail::with([
            'orderBox',
            'orderBox.box',
            'orderBox.order',
            'orderBox.order.agency',
            'orderBox.order.client',
            'orderBox.order.cold_room',
            'orderBox.order.provider',
            'orderBox.order.user',
            'product',
        ])
            ->whereHas('orderBox.order', function ($query) {
                return $query->whereBetween('flight_date', [$this->start_at, $this->end_to])
                    ->where([
                    ['user_id', 'like', '%' . $this->id . '%'],
                    ['status', 'like', '%' . $this->status . '%'],
                ]);
            })
            ->get();
        }
////////////////////DESCARGA EXCEL POR USUARIO//////////////////////////////////////
        elseif($this->role == 1 && $this->report == 1){
        if ($this->start_at =! null && $this->end_to != null) {
                return OrderDetail::with([
                'orderBox',
                'orderBox.box',
                'orderBox.order',
                'orderBox.order.agency',
                'orderBox.order.client',
                'orderBox.order.cold_room',
                'orderBox.order.provider',
                'orderBox.order.user',
                'product',
            ])
                ->whereHas('orderBox.order', function ($query) {
                    if($this->start_at =! null && $this->end_to != null) {
                    $list = $query->whereBetween('created_at', [$this->start_at.' 00:00:00', $this->end_to.' 23:59:00']);
                    }
                    if($this->status != "null") {
                       $query->where([['status', 'like', '%' . $this->status . '%']]);
                    }
                })->whereHas('orderBox.order.agency', function ($query) {
                    if ($this->agency != 'null') {
                        $query->where('name', 'like', '%' . $this->agency . '%');
                    }
                })->whereHas('orderBox.order.client', function ($query) {
                    if ($this->client != 'null') {
                        $query->where('name', 'like', '%' . $this->client . '%');
                    }
                })->get();
               return $list;
           }

           //////////PERFIL 2 ////////////////////////
        }elseif($this->role != 1 && $this->report == 1){
                if ($this->start_at =! null && $this->end_to != null) {
                return OrderDetail::with([
                'orderBox',
                'orderBox.box',
                'orderBox.order',
                'orderBox.order.agency',
                'orderBox.order.client',
                'orderBox.order.cold_room',
                'orderBox.order.provider',
                'orderBox.order.user',
                'product',
                ])
                ->whereHas('orderBox.order', function ($query) {
                     if ($this->start_at =! null && $this->end_to != null && $this->status != "null") {
                    $list = $query->whereBetween('created_at', [$this->start_at.' 00:00:00', $this->end_to.' 23:59:00'])
                     ->where([
                    ['user_id', 'like', '%' . $this->id . '%'],
                    ['status', 'like', '%' . $this->status . '%'],
                    ]);
                    }else{
                    $list = $query->whereBetween('created_at', [$this->start_at.' 00:00:00', $this->end_to.' 23:59:00'])
                     ->where([
                    ['user_id', 'like', '%' . $this->id . '%'],
                    ]);
                    }
                })->whereHas('orderBox.order.agency', function ($query) {
                    if ($this->agency != 'null') {
                        $query->where('name', 'like', '%' . $this->agency . '%');
                    }
                })->whereHas('orderBox.order.client', function ($query) {
                    if ($this->client != 'null') {
                        $query->where('name', 'like', '%' . $this->client . '%');
                    }
                })->get();
               return $list;
           }
        }

    }

    public function headingsOriginal(): array
    {
        return [
            'Orden',
            'Finca',
            'Marcación',
            'Variedad',
            'Observación',
            'Longitud',
            'Precio',
            'Tallos',
            '# de cajas',
            'Tipo de caja',
            'Agencia',
            'Cuarto frío',
            'Día de entrega en carguera',
            'Día de vuelo',
            'AWB',
            'Vendedor',
            'Estado',
        ];
    }
    public function headings(): array
    {
        return [
            
            'Marcación',
            'Cliente',
            'Variedad',
            'Longitud',
            'Tallos',
            '# de cajas',
            'Tipo de caja',
            'Tracking',
            'Master',
            'Finca',
            'Agencia',
            'Cuarto frío',
            'Día de entrega en carguera',
            'Día de vuelo',
            'AWB',
            'Vendedor',
            'Estado',
            'Creacion',
        ];
    }

    public function mapOriginal($detail): array
    {
        $status_map = (object) ([
            "R" => 'Registrado',
            "C" => 'Confimado',
            "F" => 'Facturado',
            "D" => 'Despachado',
            "O" => 'Orden Fija',
        ]);

        return [
            $detail->orderBox->order->id,
            $detail->orderBox->order->provider->name,
            $detail->orderBox->order->client->edr_code,
            $detail->product->description,
            $detail->observation,
            $detail->longitude,
            $detail->price,
            $detail->stems,
            $detail->orderBox->box_number,
            $detail->orderBox->box->name,
            $detail->orderBox->order->agency->name,
            $detail->orderBox->order->cold_room->name,
            $detail->orderBox->order->delivery_date,
            $detail->orderBox->order->flight_date,
            $detail->orderBox->order->awb,
            $detail->orderBox->order->user->name . ' ' . $detail->orderBox->order->user->surname,
            $status_map->{$detail->orderBox->order->status},
        ];
    }

    public function map($detail): array
    {
        $status_map = (object) ([
            "R" => 'Registrado',
            "C" => 'Confimado',
            "F" => 'Facturado',
            "D" => 'Despachado',
            "O" => 'Orden Fija',
        ]);

        return [
            $detail->orderBox->order->client->edr_code,
            $detail->orderBox->order->client->name.' '.$detail->orderBox->order->client->contact,
            $detail->product->description.'/'.$detail->observation,
            $detail->longitude,
            $detail->stems,
            $detail->orderBox->box_number,
            $detail->orderBox->box->name,
            '',
            '',
            $detail->orderBox->order->provider->name,
            $detail->orderBox->order->agency->name,
            $detail->orderBox->order->cold_room->name,
            $detail->orderBox->order->delivery_date,
            $detail->orderBox->order->flight_date,
            $detail->orderBox->order->awb,
            $detail->orderBox->order->user->name . ' ' . $detail->orderBox->order->user->surname,
            $status_map->{$detail->orderBox->order->status},
            $detail->orderBox->order->created_at,
        ];
    }


    
}
