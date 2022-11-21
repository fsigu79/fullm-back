<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Cobus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CobuController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function list(Request $request)
    {
        $finicio=$request['finicio'];
        $ffin=$request['ffin'];
        $tipo=$request['tipo'];

        if($tipo=='salida'){
            $sql="select * from cobus where fecha_salida between ? and ? order by fecha_salida desc;";
        }elseif($tipo=='liquidacion'){
            $sql="select * from cobus where fecha_liquidacion between ? and ? order by fecha_salida desc;";
        }else{
            return $this->getErrCustom($request, 'Tipo de reporte no valido');
        }
        $list = DB::select($sql, [$finicio, $ffin]);
        // $list = Cobus::whereBetween('fecha_salida', [$finicio, $ffin])->orderBy('razon_social', 'desc')->get();
        // $list = Cobus::orderBy('razon_social', 'desc')->orderBy('marca', 'desc')->get();
        return $this->getOk($list);
    }

    public function searchProductsCobus(Request $request)
    {
        $input = $request->all();
        $marca = $request['marca'];
        $modelo = strtoupper($request['modelo']);

        if ($request['marca'] == 'null' || $request['marca'] == '') {
            $marca = '0';
        } else {
            $marca = $request['marca'];
        }

        if ($request['modelo'] == 'null' || $request['modelo'] == '') {
            $modelo = '0';
        } else {
            $modelo = $request['modelo'];
        }

        try {
            $sql = "select distinct codigo, razon_social,marca,modelo
                from cobus
                where if ('" . $marca . "'='0',true,marca like '%" . $marca . "%') and
                        if ('" . $modelo . "'='0',true,modelo like '%" . $modelo . "%')";

            $list = DB::select($sql);
            return $this->getOk($list);
        } catch (\Exception $e) {
            return $this->insertErrCustom($request, $e->getMessage());
        }
    }



    public function create(Request $request)
    {
        try {

            $input = $request->all();
            //$params_arrray = json_decode($input,true); //consigo un objeto
            //getseriesreturn $this->getOk($input);
            //DB::beginTransaction();
            //grabamos el detalle

            foreach ($input['detalle'] as $detalle) {

                $sql = "select count(id) from cobus WHERE
                (codigo=?) and
                (razon_social=?) and
                (ruc=?) and
                (marca=?) and
                (modelo=?) and
                (referendo=?) and
                (fecha_salida=?);";

                $count = DB::select(
                    $sql,
                    [
                        $detalle['codigo'],
                        $detalle['razon_social'],
                        $detalle['ruc'],
                        $detalle['marca'],
                        $detalle['modelo'],
                        $detalle['referendo'],
                        $detalle['fecha_salida']
                    ]
                );

                if ($count[0]->count <= 0) {
                    $entidad = new Cobus($detalle);
                    $entidad->save();
                }
            };
            //DB::commit();
            return $this->insertOk($input);
        } catch (\Exception $e) {
            //DB::rollBack();
            return $this->insertErrCustom(null, $e->getMessage());
        }
    }


    public function create1(Request $request)
    {
        try {

            $input = $request->all();
            //$params_arrray = json_decode($input,true); //consigo un objeto
            //return $this->getOk($input);
            DB::beginTransaction();
            //grabamos el detalle
            foreach ($input['detalle'] as $detalle) {
                $entidad = new Cobus($detalle);
                $entidad->save();
            };
            DB::commit();
            return $this->insertOk($input);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->insertErrCustom(null, $e->getMessage());
        }
    }
}
