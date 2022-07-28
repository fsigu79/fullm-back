<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Client::with(['city', 'country'])->get();
    }

    public function headings(): array
    {
        return [
            'empresa', 
            'contacto', 
            'estado', 
            'email',
            'código EDR',
            'dirección',
            'país',
            'ciudad',
            'teléfono'
        ];
    }

    public function map($client) : array {
        $status_map = (object) (["A" => 'Activo', "I" => 'Inactivo']);
        return [
            $client->name,
            $client->contact,
            $status_map->{$client->status},
            $client->email,
            $client->edr_code,
            $client->address,
            $client->country->name,
            $client->city->name,
            $client->phone,
        ];
    }
}
