<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use PDF;


class ProductoPacController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        //$this->middleware('auth:admin');
    }


  public function listprodpac(Request $request)
    {
        $input = $request->all();
        $marca=$request['marca_id'];

        if ($request['producto_id']=='null' || $request['producto_id']==''){
            $producto='0';
        }else{
            $producto=$request['producto_id'];
        }

        if ($request['producto_nombre']=='null' || $request['producto_nombre']==''){
            $descripcion='0';
        }else{
            $descripcion=$request['producto_nombre'].'%';
        }

        //$producto=isset($request['producto_id']) ?$request['producto_id']:'0';
        //$vendedor=isset($request['vendedor_id']) ?$request['vendedor_id']:'0';


        //$descripcion=$request['producto_nombre'];
        $tipo=$request['tipo_id'];
        $categoria=$request['categoria_id'];



        $sql="select codprod01 as codigo,
                desprod01 as descripcion,
                cantact01  as saldo,
                orden01 as tipoc,
                (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab='46' AND codtab!='' AND codtab=maepro.orden01) AS tipo_producto,
                marca01 as marcac,
                (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab='4530' AND codtab!='' AND codtab=maepro.marca01) AS marca,
                catprod01 as categoriac,
                (SELECT desccate FROM jcev.categorias cc WHERE tipocate='02' AND codcate=catprod01) as categoria
            from jcev.maepro
            where  codprod01!=''
                and case when '0'='xprod' then true else codprod01='xprod' end
                and case when '0'='xdes' then true else desprod01 like 'xdes' end";

        //$list = DB::select($sql,[$producto,$producto,$descripcion,$descripcion]);
        $sql=  str_replace('xprod',$producto,$sql);
        $sql=  str_replace('xdes',$descripcion,$sql);
        $list = DB::connection('mysqlpac')->select($sql);

        return $this->getOk($list);
    }


    public function exportLabel($id)
    {
        //$sql='SELECT codprod01 as codigo,desprod01 as producto from jcev.maepro where codprod01 in("BICI308A24V","BICI89","BUZ01")';
        $sql='SELECT codprod01 as codigo,desprod01 as producto from jcev.maepro where codprod01=?';
        $order = DB::connection('mysqlpac')->select($sql,[$id]);

        $customPaper = array(0,0,567.00,283.80);
        $pdf = PDF::loadView('label',compact('order')
        )->setPaper([0, 0, 141.73,283.47 ], 'landscape');

        return $pdf->stream('label1.pdf');
    }

    public function searchProductsPac(Request $request)
    {
        $input = $request->all();
        $cod=$request['code'];
        $des=strtoupper($request['name']);

         if ($request['code']=='null' || $request['code']==''){
            $cod='0';
        }else{
            $cod=$request['code'];
        }

        if ($request['name']=='null' || $request['name']==''){
            $des='0';
        }else{
            $des=$request['name'];
        }

       try{
            $sql="SELECT 1 as id,codprod01 as codigo,desprod01 as nombre,'barcode' as barcode,'descripcion' as descripcion ,
                        cantact01 as saldo,
                        valact01 as costo_promedio,
                        0 as descuento,
                        0 as nventariado,
                        1 as tieneiva
                from jcev.maepro
                where if ('".$cod."'='0',true,codprod01 like '%".$cod."%') and
                        if ('".$des."'='0',true,desprod01 like '%".$des."%')";

            $list = DB::connection('mysqlpac')->select($sql);
            return $this->getOk($list);

        }catch(\Exception $e) {
            return $this->insertErrCustom($request, $e->getMessage());
        }
    }



     public function productIdPac($id){
        try{
            $sql="SELECT 1 as id,codprod01 as codigo,desprod01 as nombre,'barcode' as barcode,'descripcion' as descripcion ,
                            cantact01 as saldo,
                            valact01 as costo_promedio,
                            0 as descuento,
                            0 as nventariado,
                            1 as tieneiva
                    from jcev.maepro where codprod01=?";
            $list = DB::connection('mysqlpac')->select($sql,[$id]);
            return $this->getOk($list);

        }catch(\Exception $e) {
            return $this->insertErrCustom($id, $e->getMessage());
        }
    }





}
