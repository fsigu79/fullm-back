<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\SeriePac;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;



use Illuminate\Http\Request;

class CatalogoSeriesController extends Controller
{
     use FormatResponseTrait;

    public function __construct()
    {

    }

    public function seriesDisponibles(Request $request)
    {


        $input = $request->all();

        //$chasis = $input['chasis'];
        //$serie = $input['serie'];
        $producto = $input['producto_id'];
        $marca = $input['marca_id'];
        $bodega=$input['bodega_id'];
        $inicio=$input['finicio'].' 00:00:00';
        $fin=$input['ffin'].' 23:59:00';
        //return $this->getOk($input);
        if ($input['serie']=='' or is_null($input['serie'])){
            $serie ='0';
        }else{
            $serie = $input['serie'];
        }

        if ($input['chasis']=='' or is_null($input['chasis'])){
            $chasis ='0';
        }else{
            $chasis = $input['chasis'];
        }
        //return $this->getOk($input);
        $validation = Validator::make(
            $request->all(),
            [
                'finicio' => 'required',
                'ffin' => 'required',
            ],
            [
                'finicio.required' => 'la fecha incialssss es requerida.',
                'ffin.required' => 'El fecha final es requerida.',
            ]
        );

        if (!$validation->fails()) {
            try{
                //consulta catalog de series
                $sql="Select serie04 as serie,
                            tipotra04 as tipo_transaccion,
                            nocomp04 as documento,
                            codprod04 as codigo,
                            desprod01 as descripcion,
                            chasis04 as chasis,
                            coddest04 as destino,
                            valor04 as valor,
                            fecmov04 as fecha,
                            catprod01,
                            (SELECT desccate FROM jcev.categorias WHERE tipocate='02' and codcate = catprod01) as categoria,
                            anio04 as anio,
                            color04 as color,
                            cpn04 as cpn,
                            if (cvanulada04='A','ANULADA', 'FAC.'+nofact04) as estado,
                            cvanulada04 as estado1,
                            nopedido04 as pedido
                        from xbase1.maeser
                        inner join xbase1.maepro on codprod04=codprod01
                        where fecmov04>=? and fecmov04<=? and cvanulada04 in ('D')
                                and if(?='0',true,codprod04=?)
                                and if(?='0',true,serie04=?)
                                and if(?='0',true,chasis04=?)
                        order by serie04 ASC";

                $sql=  str_replace('xbase1',$bodega,$sql);
                $list = DB::connection('mysqlpac')->select($sql,
                                                    [$inicio,
                                                    $fin,
                                                    $producto,
                                                    $producto,
                                                    $serie,
                                                    $serie,
                                                    $chasis,
                                                    $chasis
                                                    ]);
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
