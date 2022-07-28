<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\SeriePac;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;



class ConsultaExtPacController extends Controller
{
    use FormatResponseTrait;

    public function datosPorChasis(Request $request)
    {
        $input = $request->all();
        $chasis = $request['chasis'];
        $serie = $request['serie'];

        $validation = Validator::make(
            $request->all(),
            [
                'chasis' => 'required',
            ],
            [
                'chasis.required' => 'El chasis es requerido.',
            ]
        );

        if (!$validation->fails()) {
            try{
                $sql="select  desprod01 as modelo,
                                serie04 as motor,
                                chasis04 as chasis,
                                anio04 as anio,
                                color04 as color,
                                cpn04 as cpn,
                                ramv04 as ramv,
                                cvanulada04,
                                codprod04
                        from jcevgyeassem.maeser, maepro
                        where codprod04=codprod01 and chasis04=?";
                        //and cvanulada04='T'"; validar que es este campo con alexis

                $list = DB::connection('mysqlpac')->select($sql,[$chasis]);
                return $this->getOk($list);
            } catch (\Exception $e) {
            return $this->insertErrCustom($input, $e->getMessage());
            }
        }
        else
        {
            return $this->insertErrCustom($validation->messages(), 'Datos inválidos');
        }
    }



}
