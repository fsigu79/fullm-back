<?php

namespace App\Exports;

use App\Models\Provider;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProviderExport implements FromCollection, WithMapping, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Provider::with('city')->get();
    }

    public function headings(): array
    {
        return [
            'nombre', 
            'email', 
            'contacto', 
            'dirección',
            'teléfono',
            'ciudad',
        ];
    }

    public function map($provider) : array {
        return [
            $provider->name,
            $provider->email,
            $provider->contact,
            $provider->address,
            $provider->phone,
            $provider->city->name,
        ];
    }
}
