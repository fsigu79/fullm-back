<?php

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
        $this->middleware('auth:admin',['except' =>
              [
                  'datosPorChasis',
                  ]]);
    }

    public function seriesDisponibles(Request $request)
    {
        $input = $request->all();
        $chasis = $request['chasis'];
        $serie = $request['serie'];
        $producto = $request['product_id'];
        $marca = $request['marca_id'];
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';

        $validation = Validator::make(
            $request->all(),
            [
                'finicio' => 'required',
                'ffin' => 'required',
            ],
            [
                'finicio.required' => 'la fecha incial es requerida.',
                'ffin.required' => 'El fecha final es requerida.',
            ]
        );

        if (!$validation->fails()) {
            try{
                //consulta catalog de series
                $sql=" Select serie04 as serie,
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
                            cvanulada04,
                            if (cvanulada04='A','ANULADA', 'FAC.'+nofact04) as estado,
                            nopedido04 as pedido,
                            nocte31 as codigo_cliente,
                            nomcte31 as cliente,
                            fecfact31 as fecha_factura

                        from jcevgyeassem.maeser
                        inner join jcevgyeassem.maepro on codprod04=codprod01
                        left join jcevgyeassem.maefac on nofact31=nofact04
                        where fecmov04>=? and fecmov04<=? and cvanulada04='D'
                        order by serie04 ASC   ";
                $list = DB::connection('mysqlpac')->select($sql,[$inicio,$fin]);
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
