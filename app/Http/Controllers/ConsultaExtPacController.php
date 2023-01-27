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

     public function __construct()
    {
        $this->middleware('auth:admin',['except' =>
              [
                  'datosPorChasis',
                  ]]);
    }


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



    public function catalogoSeriesAnterior(Request $request)
    {
        $input = $request->all();
        $chasis = $request['chasis'];
        $serie = $request['serie'];
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
                $sql="Select serie04 as serie,tipotra04 as tipo_transaccion,nocomp04 as documento,
                            codprod04 as codigo,desprod01 as descripcion,
                            chasis04 as chasis,coddest04 as destino,valor04 as valor,
                            fecmov04 as fecha,
                            catprod01,
                            (SELECT desccate FROM jcev.categorias WHERE tipocate='02' and codcate = catprod01) as categoria,
                            anio04 as anio,
                            color04 as color,
                            cpn04 as cpn,
                            cvanulada04,
                            if (cvanulada04='A','ANULADA', 'FAC.'+nofact04) as estado,
                            nopedido04 as pedido
                        from jcevgyeassem.maeser, jcevgyeassem.maepro
                        where codprod04=codprod01 and fecmov04>=? and fecmov04<=?
                        order by serie04 ASC";
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


    public function catalogoSeries(Request $request)
    {
        $input = $request->all();
        $chasis = $request['chasis'];
        $serie = $request['serie'];
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
                $sql=" Select serie04 as serie,tipotra04 as tipo_transaccion,nocomp04 as documento,
                            codprod04 as codigo,desprod01 as descripcion,
                            chasis04 as chasis,coddest04 as destino,valor04 as valor,
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
                        where fecmov04>=? and fecmov04<=?
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

    public function catalogoSeriesByCodCliente(Request $request)
    {
        $input = $request->all();
        $cliente = $request['cod_client'];
        $serie = $request['serie'];
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';

        $validation = Validator::make(
            $request->all(),
            [
                'cod_client' => 'required',
            ],
            [
                'cod_client.required' => 'El codigo del cliente es requerido.',

            ]
        );

        if (!$validation->fails()) {
            try{
                //consulta catalog de series
                $sql=" Select serie04 as serie,tipotra04 as tipo_transaccion,nocomp04 as documento,
                            codprod04 as codigo,desprod01 as descripcion,
                            chasis04 as chasis,coddest04 as destino,valor04 as valor,
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
                        where nocte31=?
                        order by serie04 ASC   ";
                $list = DB::connection('mysqlpac')->select($sql,[$cliente]);
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

    public function catalogoSeriesByCliente(Request $request)
    {
        $input = $request->all();
        $cliente = $request['client'].'%';
        $serie = $request['serie'];
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';

        $validation = Validator::make(
            $request->all(),
            [
                'client' => 'required',
            ],
            [
                'client.required' => 'El nombre del cliente es requerido.',

            ]
        );

        if (!$validation->fails()) {
            try{
                //consulta catalog de series
                $sql=" Select serie04 as serie,tipotra04 as tipo_transaccion,nocomp04 as documento,
                            codprod04 as codigo,desprod01 as descripcion,
                            chasis04 as chasis,coddest04 as destino,valor04 as valor,
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
                        where nomcte31 like ?
                        order by serie04 ASC   ";
                $list = DB::connection('mysqlpac')->select($sql,[$cliente]);
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


    public function catalogoSeriesByFechaFactura(Request $request)
    {
        $input = $request->all();
        $serie = $request['serie'];
        $inicio=$request['fecha'].' 00:00:00';
        $fin=$request['fecha'].' 23:59:00';

        $validation = Validator::make(
            $request->all(),
            [
                'fecha' => 'required',
            ],
            [
                'fecha.required' => 'La fecha de la factura es requerida.',

            ]
        );

        if (!$validation->fails()) {
            try{
                //consulta catalog de series
                $sql=" Select serie04 as serie,tipotra04 as tipo_transaccion,nocomp04 as documento,
                            codprod04 as codigo,desprod01 as descripcion,
                            chasis04 as chasis,coddest04 as destino,valor04 as valor,
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
                        where fecmov04>=? and fecmov04<=?
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


    public function catalogoSeriesByChasis(Request $request)
    {
        $input = $request->all();
        $chasis = $request['chasis'];
        $inicio=$request['fecha'].' 00:00:00';
        $fin=$request['fecha'].' 23:59:00';

        $validation = Validator::make(
            $request->all(),
            [
                'chasis' => 'required',
            ],
            [
                'chasis.required' => 'El numero de chasis es requerido.',

            ]
        );

        if (!$validation->fails()) {
            try{
                //consulta catalog de series
                $sql=" Select serie04 as serie,tipotra04 as tipo_transaccion,nocomp04 as documento,
                            codprod04 as codigo,desprod01 as descripcion,
                            chasis04 as chasis,coddest04 as destino,valor04 as valor,
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
                        where chasis04=?
                        order by serie04 ASC   ";
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



 public function productosTodomoto(Request $request)
    {
        $input = $request->all();
        $chasis = $request['codigo'];
        $serie = $request['serie'];

        /*$validation = Validator::make(
            $request->all(),
            [
                'chasis' => 'required',
            ],
            [
                'chasis.required' => 'El chasis es requerido.',
            ]
        );*/

        //if (!$validation->fails()) {
            try{
                //consulta catalog de series
                $sql="select
                            codprod01 as codigo,
                        desprod01 as nombre,
                        (SELECT DISTINCT nomtab FROM todomoto.maetab WHERE numtab='46' AND codtab<>'' AND codtab=maepro.orden01) AS tipo_producto,
                        (SELECT desccate FROM categorias cc WHERE tipocate='02' AND codcate=catprod01) as categoria,
                        (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab='4530' AND codtab<>'' AND codtab=maepro.marca01) AS marca,
                        '' as codigo_barra,
                        12 as tiempo_garantia,
                        precvta01 as precio1,
                        false as impuesto_cliente,
                        0 as costo,
                        cantact01 as stock
                    from todomoto.maepro";
                $list = DB::connection('mysqlpac')->select($sql,[]);
                return $this->getOk($list);
            } catch (\Exception $e) {
            return $this->insertErrCustom($input, $e->getMessage());
            }
       // }
        //else
        //{
          //  return $this->insertErrCustom($validation->messages(), 'Datos inválidos');
        //}
    }





}
