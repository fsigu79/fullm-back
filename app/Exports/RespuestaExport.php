<?php

namespace App\Exports;

use App\Models\Respuesta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RespuestaExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function  __construct()
    {

    }

    public function collection()
    {
        return Respuesta::with('user','client','respuestas5')->get();
    }


    public function headings(): array
    {
        return [

            'Codigo',
            'Ruc',
            'Nombre',
            'Medidor',
            'Provincia',
            'Canton',
            'Area',
            'Sector',
            'Parroquia',
            'Comunidad',
            'Zona',
            'Este',
            'Norte',
            'username',
            'Se puede encuestar',
            'Porque NO',
            'Es Informante',
            'Titular Fallecido',
            'Nombre Informante',
            'Apellido informante',
            'Cedula informante',
            'Telefono',
            'Celular',
            'correo',
            //'Miembros hogar',
            'No Hogares Adicionales',
            'Tipo Vivienda',
            'Otro Tipo Vivienda',
            'Tiene Departamentos',
            'No Departamentos',
            'Via acceso',
            'Material Pared',
            'Otro Material Pared',
            'Material Techo',
            'Otro Material Techo',
            'Material Piso',
            'Estado techo',
            'Estado pared',
            'Estado piso',
            'La vivienda actual es',
        ];
    }

    public function map($detail): array
    {
        return [
            $detail->client->codigo,
            $detail->client->ruc,
            $detail->client->nombre,
            $detail->client->medidor,
            $detail->client->provincia,
            $detail->client->canton,
            $detail->client->area,
            $detail->client->sector,
            $detail->client->parroquia,
            $detail->client->comunidad,
            $detail->client->zona,
            $detail->client->este,
            $detail->client->norte,
            $detail->user->username,
            $detail->p33, //'Se puede encuestar',
            $detail->p33m, //'Porque NO',
            $detail->p4, //'Es Informante',
            $detail->p4, //'Es Informante',
            $detail->p401,//'Titular Fallecido',
            $detail->p41,//'Nombre Informante',
            $detail->p42,//'Apellido informante',
            $detail->p43,//'Cedula informante',
            $detail->p44,//'Telefono',
            $detail->p45,//'Celular',
            $detail->p46,//'correo',
            //$detail->respuestas5->p5nombre,,//'Miembros hogar',
            $detail->p52,//'No Hogares Adicionales',
            $detail->p61,//'Tipo Vivienda',
            $detail->p611,//'Otro Tipo Vivienda',
            $detail->p62,//'Tiene Departamentos',
            $detail->p621,//'No Departamentos',
            $detail->p63,//'Via acceso',
            $detail->p64,//'Material Pared',
            $detail->p641,//'Otro Material Pared',
            $detail->p65,//'Material Techo',
            $detail->p651,//'Otro Material Techo',
            $detail->p66,//'Material Piso',
            $detail->p67techo,
            $detail->p67pared,
            $detail->p67piso,
            $detail->p71,
            //$status_map->{$detail->orderBox->order->status},
        ];
    }





}
