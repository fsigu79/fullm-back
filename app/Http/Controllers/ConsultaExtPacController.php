<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\SeriePac;
use App\Models\FacturaRpa;
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


    public function productosJcevList(Request $request)
    {
        $input = $request->all();
        $chasis = $request['codigo'];
        $serie = $request['serie'];
            try{
                //consulta catalog de series
                $sql="  select
                        codprod01 as codigo,
                        desprod01 as nombre,
                        (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab='46' AND codtab<>'' AND codtab=maepro.orden01) AS tipo_producto,
                        (SELECT desccate FROM categorias cc WHERE tipocate='02' AND codcate=catprod01) as categoria,
                        (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab='4530' AND codtab<>'' AND codtab=maepro.marca01) AS marca,
                        precvta01 as precio1,
                        precio201 as precio2,
                        precio301 as precio3,
                        (cantact01+
                        IFNULL((SELECT cantact01 FROM jcevcuenca2.maepro WHERE jcev.maepro.codprod01=jcevcuenca2.maepro.codprod01),0) +
                            IFNULL((SELECT cantact01 FROM jcevgye1.maepro WHERE jcev.maepro.codprod01=jcevgye1.maepro.codprod01),0) +
                            IFNULL((SELECT cantact01 FROM jcevuio1.maepro WHERE jcev.maepro.codprod01=jcevuio1.maepro.codprod01),0) +
                            IFNULL((SELECT cantact01 FROM jcevconsigvirt.maepro WHERE jcev.maepro.codprod01=jcevconsigvirt.maepro.codprod01),0) +
                            IFNULL((SELECT cantact01 FROM jcevstecvir.maepro WHERE jcev.maepro.codprod01=jcevstecvir.maepro.codprod01),0) +
                            IFNULL((SELECT cantact01 FROM jcevgyeassem.maepro WHERE jcev.maepro.codprod01=jcevgyeassem.maepro.codprod01),0) +
                            IFNULL((SELECT cantact01 FROM jcevcuenca1.maepro WHERE jcev.maepro.codprod01=jcevcuenca1.maepro.codprod01),0)  +
                            IFNULL((SELECT cantact01 FROM jcevgye10.maepro WHERE jcev.maepro.codprod01=jcevgye10.maepro.codprod01),0)+
                            IFNULL((SELECT cantact01 FROM jcevgye3.maepro WHERE jcev.maepro.codprod01=jcevgye3.maepro.codprod01),0)
                        ) as stock_total,
                        cantact01 AS smatriz,
                        IFNULL((SELECT cantact01 FROM jcevcuenca2.maepro WHERE jcev.maepro.codprod01=jcevcuenca2.maepro.codprod01),0) AS scuenca2,
                            IFNULL((SELECT cantact01 FROM jcevgye1.maepro WHERE jcev.maepro.codprod01=jcevgye1.maepro.codprod01),0) AS sguay1,
                            IFNULL((SELECT cantact01 FROM jcevuio1.maepro WHERE jcev.maepro.codprod01=jcevuio1.maepro.codprod01),0) AS squito1,
                            IFNULL((SELECT cantact01 FROM jcevconsigvirt.maepro WHERE jcev.maepro.codprod01=jcevconsigvirt.maepro.codprod01),0) AS sconsvirt,
                            IFNULL((SELECT cantact01 FROM jcevstecvir.maepro WHERE jcev.maepro.codprod01=jcevstecvir.maepro.codprod01),0) AS sservtecn,
                            IFNULL((SELECT cantact01 FROM jcevgyeassem.maepro WHERE jcev.maepro.codprod01=jcevgyeassem.maepro.codprod01),0) AS sgyeasse,
                            IFNULL((SELECT cantact01 FROM jcevcuenca1.maepro WHERE jcev.maepro.codprod01=jcevcuenca1.maepro.codprod01),0) AS sunicomer,
                            IFNULL((SELECT cantact01 FROM jcevgye10.maepro WHERE jcev.maepro.codprod01=jcevgye10.maepro.codprod01),0) AS sguay2,
                            IFNULL((SELECT cantact01 FROM jcevgye3.maepro WHERE jcev.maepro.codprod01=jcevgye3.maepro.codprod01),0) AS sguy3
                    from jcev.maepro
                    order by desprod01";
                $list = DB::connection('mysqlpac')->select($sql,[]);
                return $this->getOk($list);
            } catch (\Exception $e) {
                return $this->insertErrCustom($input, $e->getMessage());
            }
    }

    public function facturaPorNumero(Request $request)
    {
        $input = $request->all();
        $serie = $input['serie'];
        $numero = $input['numero'];
        $comprobante=$serie.'-'.$numero;
        $base='jcev';

            try{

                if ($serie=='001-100'){
                    $base='jcev';
                } else if ($serie=='001-102') {
                    $base='jcevgye1';
                } else if ($serie=='001-103') {
                    $base='jcevgyeassem';
                } else if ($serie=='001-104') {
                    $base='jcevuio1';
                } else if ($serie=='001-105') {
                    $base='jcevcuenca2';
                } else if ($serie=='001-106') {
                    $base='jcevcuenca1';
                } else if ($serie=='001-114') {
                    $base='jcevgye3';
                } else if ($serie=='001-107') {
                    $base='jcevgye10';
                } else if ($serie=='001-108') {
                    $base='jcevstecvir';
                 } else if ($serie=='001-109') {
                    $base='jcevstecguay';
                 } else if ($serie=='001-111') {
                    $base='jcevpromo';
                 } else if ($serie=='001-108') {
                    $base='jcevstecvir';
                 } else if ($serie=='001-108') {
                    $base='jcevstecvir';
                } else {
                    $base='jcev';
                }
                //consulta catalog de series
                $sql="select tipodocto31,nofact31,nocte31,nomcte31,vtabta31 as subcab,if (cvanulado31=9,'ANULADO','FACTURADO') as estadofac,
                        descto31,
                        (select sum(round(precvta03-descvta03,2)) from xbase.movpro where tipotra03='80' and nofact31=nocomp03) as subtotal,
                        (select round(sum( (iva03/100)*(precvta03 - descvta03-desctotvta03)),2) from xbase.movpro where tipotra03='80' and nofact31=nocomp03) as iva
                        from xbase.maefac where tipodocto31=02 and nofact31=?";

                $query=  str_replace('xbase',$base,$sql);


                $fact = DB::connection('mysqlpac')->select($query,[$comprobante]);


                $factura= new FacturaRpa();
                if (!empty($fact)) {
                    $factura->tipodocto31=$fact[0]->tipodocto31;
                    $factura->nofact31=$fact[0]->nofact31;
                    $factura->nocte31=$fact[0]->nocte31;
                    $factura->nomcte31=$fact[0]->nomcte31;
                    $factura->descto31=$fact[0]->descto31;
                    $factura->subcab=$fact[0]->subcab;
                    $factura->estadofac=$fact[0]->estadofac;
                    $factura->subtotal=$fact[0]->subtotal;
                    $factura->iva=$fact[0]->iva;

                    $sql="select cte.codcte01,cte.agente_retencion,ar.descripcion,bienes_iva,servicios_iva,bienes_renta,servicios_renta
                        from jcev.maecte cte
                        inner join jcev.agentes_retencion ar on cte.agente_retencion =ar.id
                        inner join jcev.configuracion_agentes_retencion car on ar.id=car.id_agente_retencion
                        where codcte01=?";

                    $reten = DB::connection('mysqlpac')->select($sql,[$factura->nocte31]);
                    if (!empty($reten)){
                        $factura->agente_retencion=$reten[0]->agente_retencion;
                        $factura->descripcion=$reten[0]->descripcion;
                        $factura->bienes_iva=$reten[0]->bienes_iva;
                        $factura->servicios_iva=$reten[0]->servicios_iva;
                        $factura->bienes_renta=$reten[0]->bienes_renta;
                        $factura->servicios_renta=$reten[0]->servicios_renta;
                    }
                    else{
                         $factura->agente_retencion='';
                        $factura->descripcion='';
                        $factura->bienes_iva='';
                        $factura->servicios_iva='';
                        $factura->bienes_renta='';
                        $factura->servicios_renta='';
                    }
                    return $this->getOk($factura);
                }else{
                    return $this->getOk($fact);
                }



            } catch (\Exception $e) {
                return $this->insertErrCustom($input, $e->getMessage());
            }
    }


    public function facturaPorNumeroCredimport(Request $request)
    {
        $input = $request->all();
        $serie = $input['serie'];
        $numero = $input['numero'];
        $comprobante=$serie.'-'.$numero;
        $base='vintipart';

            try{

                if ($serie=='001-101'){
                    $base='vintipart';
                } else if ($serie=='001-102') {
                    $base='vintipartcuen1';
                } else if ($serie=='001-104') {
                    $base='vintipartgyeass';
                } else if ($serie=='001-100') {
                    $base='vintipartuio';
                } else if ($serie=='001-001') {
                    $base='vintipartcvirt';
                } else if ($serie=='001-103') {
                    $base='vintipartgye3';
                } else {
                    $base='vintipart';
                }
                //consulta catalog de series
                $sql="select tipodocto31,nofact31,nocte31,nomcte31,vtabta31 as subcab,if (cvanulado31=9,'ANULADO','FACTURADO') as estadofac,
                        descto31,
                        (select sum(round(precvta03-descvta03,2)) from xbase.movpro where tipotra03='80' and nofact31=nocomp03) as subtotal,
                        (select round(sum( (iva03/100)*(precvta03 - descvta03-desctotvta03)),2) from xbase.movpro where tipotra03='80' and nofact31=nocomp03) as iva
                        from xbase.maefac where tipodocto31=02 and nofact31=?";

                $query=  str_replace('xbase',$base,$sql);


                $fact = DB::connection('mysqlpac')->select($query,[$comprobante]);


                $factura= new FacturaRpa();
                if (!empty($fact)) {
                    $factura->tipodocto31=$fact[0]->tipodocto31;
                    $factura->nofact31=$fact[0]->nofact31;
                    $factura->nocte31=$fact[0]->nocte31;
                    $factura->nomcte31=$fact[0]->nomcte31;
                    $factura->descto31=$fact[0]->descto31;
                    $factura->subcab=$fact[0]->subcab;
                    $factura->estadofac=$fact[0]->estadofac;
                    $factura->subtotal=$fact[0]->subtotal;
                    $factura->iva=$fact[0]->iva;

                    $sql="select cte.codcte01,cte.agente_retencion,ar.descripcion,bienes_iva,servicios_iva,bienes_renta,servicios_renta
                        from vintipart.maecte cte
                        inner join vintipart.agentes_retencion ar on cte.agente_retencion =ar.id
                        inner join vintipart.configuracion_agentes_retencion car on ar.id=car.id_agente_retencion
                        where codcte01=?";

                    $reten = DB::connection('mysqlpac')->select($sql,[$factura->nocte31]);
                    if (!empty($reten)){
                        $factura->agente_retencion=$reten[0]->agente_retencion;
                        $factura->descripcion=$reten[0]->descripcion;
                        $factura->bienes_iva=$reten[0]->bienes_iva;
                        $factura->servicios_iva=$reten[0]->servicios_iva;
                        $factura->bienes_renta=$reten[0]->bienes_renta;
                        $factura->servicios_renta=$reten[0]->servicios_renta;
                    }
                    else{
                         $factura->agente_retencion='';
                        $factura->descripcion='';
                        $factura->bienes_iva='';
                        $factura->servicios_iva='';
                        $factura->bienes_renta='';
                        $factura->servicios_renta='';
                    }
                    return $this->getOk($factura);
                }else{
                    return $this->getOk($fact);
                }



            } catch (\Exception $e) {
                return $this->insertErrCustom($input, $e->getMessage());
            }
    }


    public function facturaPorNumeroEvisu(Request $request)
    {
        $input = $request->all();
        $serie = $input['serie'];
        $numero = $input['numero'];
        $comprobante=$serie.'-'.$numero;
        $base='distevisu';

            try{

                if ($serie=='001-501'){
                    $base='distevisu';
                } else if ($serie=='001-105') {
                    $base='distevisucuenca2';
                } else if ($serie=='001-102') {
                    $base='distevisugy1';
                } else if ($serie=='001-104') {
                    $base='distevisuuio1';
                } else if ($serie=='999-004') {
                    $base='distevisuconsigvirt';
                } else if ($serie=='001-108') {
                    $base='distevisustecvir';
                } else if ($serie=='001-103') {
                    $base='distevisugyeassem';
                } else if ($serie=='001-106') {
                    $base='distevisucuenca1';
                } else if ($serie=='001-107') {
                    $base='distevisugye10';
                } else {
                    $base='distevisu';
                }
                //consulta catalog de series
                $sql="select tipodocto31,nofact31,nocte31,nomcte31,vtabta31 as subcab,if (cvanulado31=9,'ANULADO','FACTURADO') as estadofac,
                        descto31,
                        (select sum(round(precvta03-descvta03,2)) from xbase.movpro where tipotra03='80' and nofact31=nocomp03) as subtotal,
                        (select round(sum( (iva03/100)*(precvta03 - descvta03-desctotvta03)),2) from xbase.movpro where tipotra03='80' and nofact31=nocomp03) as iva
                        from xbase.maefac where tipodocto31=02 and nofact31=?";

                $query=  str_replace('xbase',$base,$sql);


                $fact = DB::connection('mysqlpac')->select($query,[$comprobante]);


                $factura= new FacturaRpa();
                if (!empty($fact)) {
                    $factura->tipodocto31=$fact[0]->tipodocto31;
                    $factura->nofact31=$fact[0]->nofact31;
                    $factura->nocte31=$fact[0]->nocte31;
                    $factura->nomcte31=$fact[0]->nomcte31;
                    $factura->descto31=$fact[0]->descto31;
                    $factura->subcab=$fact[0]->subcab;
                    $factura->estadofac=$fact[0]->estadofac;
                    $factura->subtotal=$fact[0]->subtotal;
                    $factura->iva=$fact[0]->iva;

                    $sql="select cte.codcte01,cte.agente_retencion,ar.descripcion,bienes_iva,servicios_iva,bienes_renta,servicios_renta
                        from distevisu.maecte cte
                        inner join distevisu.agentes_retencion ar on cte.agente_retencion =ar.id
                        inner join distevisu.configuracion_agentes_retencion car on ar.id=car.id_agente_retencion
                        where codcte01=?";

                    $reten = DB::connection('mysqlpac')->select($sql,[$factura->nocte31]);
                    if (!empty($reten)){
                        $factura->agente_retencion=$reten[0]->agente_retencion;
                        $factura->descripcion=$reten[0]->descripcion;
                        $factura->bienes_iva=$reten[0]->bienes_iva;
                        $factura->servicios_iva=$reten[0]->servicios_iva;
                        $factura->bienes_renta=$reten[0]->bienes_renta;
                        $factura->servicios_renta=$reten[0]->servicios_renta;
                    }
                    else{
                         $factura->agente_retencion='';
                        $factura->descripcion='';
                        $factura->bienes_iva='';
                        $factura->servicios_iva='';
                        $factura->bienes_renta='';
                        $factura->servicios_renta='';
                    }
                    return $this->getOk($factura);
                }else{
                    return $this->getOk($fact);
                }



            } catch (\Exception $e) {
                return $this->insertErrCustom($input, $e->getMessage());
            }
    }

    public function facturaPorNumeroElectrotienda(Request $request)
    {
        $input = $request->all();
        $serie = $input['serie'];
        $numero = $input['numero'];
        $comprobante=$serie.'-'.$numero;
        $base='electrot';

            try{

                if ($serie=='001-003'){
                    $base='electrot';
                } else if ($serie=='999-001') {
                    $base='electrotgyeass';
                } else if ($serie=='999-002') {
                    $base='electrotcvirt';
                } else if ($serie=='999-003') {
                    $base='electrotstvirt';
                } else {
                    $base='electrot';
                }
                //consulta catalog de series
                $sql="select tipodocto31,nofact31,nocte31,nomcte31,vtabta31 as subcab,if (cvanulado31=9,'ANULADO','FACTURADO') as estadofac,
                        descto31,
                        (select sum(round(precvta03-descvta03,2)) from xbase.movpro where tipotra03='80' and nofact31=nocomp03) as subtotal,
                        (select round(sum( (iva03/100)*(precvta03 - descvta03-desctotvta03)),2) from xbase.movpro where tipotra03='80' and nofact31=nocomp03) as iva
                        from xbase.maefac where tipodocto31=02 and nofact31=?";

                $query=  str_replace('xbase',$base,$sql);


                $fact = DB::connection('mysqlpac')->select($query,[$comprobante]);


                $factura= new FacturaRpa();
                if (!empty($fact)) {
                    $factura->tipodocto31=$fact[0]->tipodocto31;
                    $factura->nofact31=$fact[0]->nofact31;
                    $factura->nocte31=$fact[0]->nocte31;
                    $factura->nomcte31=$fact[0]->nomcte31;
                    $factura->descto31=$fact[0]->descto31;
                    $factura->subcab=$fact[0]->subcab;
                    $factura->estadofac=$fact[0]->estadofac;
                    $factura->subtotal=$fact[0]->subtotal;
                    $factura->iva=$fact[0]->iva;

                    $sql="select cte.codcte01,cte.agente_retencion,ar.descripcion,bienes_iva,servicios_iva,bienes_renta,servicios_renta
                        from electrot.maecte cte
                        inner join electrot.agentes_retencion ar on cte.agente_retencion =ar.id
                        inner join electrot.configuracion_agentes_retencion car on ar.id=car.id_agente_retencion
                        where codcte01=?";

                    $reten = DB::connection('mysqlpac')->select($sql,[$factura->nocte31]);
                    if (!empty($reten)){
                        $factura->agente_retencion=$reten[0]->agente_retencion;
                        $factura->descripcion=$reten[0]->descripcion;
                        $factura->bienes_iva=$reten[0]->bienes_iva;
                        $factura->servicios_iva=$reten[0]->servicios_iva;
                        $factura->bienes_renta=$reten[0]->bienes_renta;
                        $factura->servicios_renta=$reten[0]->servicios_renta;
                    }
                    else{
                         $factura->agente_retencion='';
                        $factura->descripcion='';
                        $factura->bienes_iva='';
                        $factura->servicios_iva='';
                        $factura->bienes_renta='';
                        $factura->servicios_renta='';
                    }
                    return $this->getOk($factura);
                }else{
                    return $this->getOk($fact);
                }



            } catch (\Exception $e) {
                return $this->insertErrCustom($input, $e->getMessage());
            }
    }

    public function facturaPorNumeroTodoMoto(Request $request)
    {
        $input = $request->all();
        $serie = $input['serie'];
        $numero = $input['numero'];
        $comprobante=$serie.'-'.$numero;
        $base='todomoto';

            try{

                if ($serie=='001-501'){
                    $base='todomoto';
                } else if ($serie=='001-103') {
                    $base='todomotogye1';
                } else if ($serie=='001-104') {
                    $base='todomotouio1';
                } else {
                    $base='todomoto';
                }
                //consulta catalog de series
                $sql="select tipodocto31,nofact31,nocte31,nomcte31,vtabta31 as subcab,if (cvanulado31=9,'ANULADO','FACTURADO') as estadofac,
                        descto31,
                        ((select sum(round(precvta03-descvta03,2)) from xbase.movpro where tipotra03='80' and nofact31=nocomp03)-xbase.maefac.descto31-xbase.maefac.desctofp31) as subtotal,
                        (select round(sum( (iva03/100)*(precvta03 - descvta03-desctotvta03)),2) from xbase.movpro where tipotra03='80' and nofact31=nocomp03) as iva
                        from xbase.maefac where tipodocto31=02 and nofact31=?";

                $query=  str_replace('xbase',$base,$sql);


                $fact = DB::connection('mysqlpac')->select($query,[$comprobante]);


                $factura= new FacturaRpa();
                if (!empty($fact)) {
                    $factura->tipodocto31=$fact[0]->tipodocto31;
                    $factura->nofact31=$fact[0]->nofact31;
                    $factura->nocte31=$fact[0]->nocte31;
                    $factura->nomcte31=$fact[0]->nomcte31;
                    $factura->descto31=$fact[0]->descto31;
                    $factura->subcab=$fact[0]->subcab;
                    $factura->estadofac=$fact[0]->estadofac;
                    $factura->subtotal=$fact[0]->subtotal;
                    $factura->iva=$fact[0]->iva;

                    $sql="select cte.codcte01,cte.agente_retencion,ar.descripcion,bienes_iva,servicios_iva,bienes_renta,servicios_renta
                        from todomoto.maecte cte
                        inner join todomoto.agentes_retencion ar on cte.agente_retencion =ar.id
                        inner join todomoto.configuracion_agentes_retencion car on ar.id=car.id_agente_retencion
                        where codcte01=?";

                    $reten = DB::connection('mysqlpac')->select($sql,[$factura->nocte31]);
                    if (!empty($reten)){
                        $factura->agente_retencion=$reten[0]->agente_retencion;
                        $factura->descripcion=$reten[0]->descripcion;
                        $factura->bienes_iva=$reten[0]->bienes_iva;
                        $factura->servicios_iva=$reten[0]->servicios_iva;
                        $factura->bienes_renta=$reten[0]->bienes_renta;
                        $factura->servicios_renta=$reten[0]->servicios_renta;
                    }
                    else{
                         $factura->agente_retencion='';
                        $factura->descripcion='';
                        $factura->bienes_iva='';
                        $factura->servicios_iva='';
                        $factura->bienes_renta='';
                        $factura->servicios_renta='';
                    }
                    return $this->getOk($factura);
                }else{
                    return $this->getOk($fact);
                }



            } catch (\Exception $e) {
                return $this->insertErrCustom($input, $e->getMessage());
            }
    }

    public function facturaPorNumeroUltracem(Request $request)
    {
        $input = $request->all();
        $serie = $input['serie'];
        $numero = $input['numero'];
        $comprobante=$serie.'-'.$numero;
        $base='ultcem';

            try{

                if ($serie=='001-100'){
                    $base='ultcem';
                } else if ($serie=='001-105') {
                    $base='ultcemcuenca2';
                } else if ($serie=='001-102') {
                    $base='ultcemgye1';
                } else if ($serie=='001-104') {
                    $base='ultcemuio1';
                } else if ($serie=='001-108') {
                    $base='ultcemstecvirt';
                } else if ($serie=='001-103') {
                    $base='ultcemgyeassem';
                } else if ($serie=='001-106') {
                    $base='ultcemcuenca1';
                } else if ($serie=='001-107') {
                    $base='ultcemgye10';
                } else if ($serie=='999-004') {
                    $base='ultcemconsigvirt';
                } else {
                    $base='ultcem';
                }
                //consulta catalog de series
                $sql="select tipodocto31,nofact31,nocte31,nomcte31,vtabta31 as subcab,if (cvanulado31=9,'ANULADO','FACTURADO') as estadofac,
                        descto31,
                        (select sum(round(precvta03-descvta03,2)) from xbase.movpro where tipotra03='80' and nofact31=nocomp03) as subtotal,
                        (select round(sum( (iva03/100)*(precvta03 - descvta03-desctotvta03)),2) from xbase.movpro where tipotra03='80' and nofact31=nocomp03) as iva
                        from xbase.maefac where tipodocto31=02 and nofact31=?";

                $query=  str_replace('xbase',$base,$sql);


                $fact = DB::connection('mysqlpac')->select($query,[$comprobante]);


                $factura= new FacturaRpa();
                if (!empty($fact)) {
                    $factura->tipodocto31=$fact[0]->tipodocto31;
                    $factura->nofact31=$fact[0]->nofact31;
                    $factura->nocte31=$fact[0]->nocte31;
                    $factura->nomcte31=$fact[0]->nomcte31;
                    $factura->descto31=$fact[0]->descto31;
                    $factura->subcab=$fact[0]->subcab;
                    $factura->estadofac=$fact[0]->estadofac;
                    $factura->subtotal=$fact[0]->subtotal;
                    $factura->iva=$fact[0]->iva;

                    $sql="select cte.codcte01,cte.agente_retencion,ar.descripcion,bienes_iva,servicios_iva,bienes_renta,servicios_renta
                        from ultcem.maecte cte
                        inner join ultcem.agentes_retencion ar on cte.agente_retencion =ar.id
                        inner join ultcem.configuracion_agentes_retencion car on ar.id=car.id_agente_retencion
                        where codcte01=?";

                    $reten = DB::connection('mysqlpac')->select($sql,[$factura->nocte31]);
                    if (!empty($reten)){
                        $factura->agente_retencion=$reten[0]->agente_retencion;
                        $factura->descripcion=$reten[0]->descripcion;
                        $factura->bienes_iva=$reten[0]->bienes_iva;
                        $factura->servicios_iva=$reten[0]->servicios_iva;
                        $factura->bienes_renta=$reten[0]->bienes_renta;
                        $factura->servicios_renta=$reten[0]->servicios_renta;
                    }
                    else{
                         $factura->agente_retencion='';
                        $factura->descripcion='';
                        $factura->bienes_iva='';
                        $factura->servicios_iva='';
                        $factura->bienes_renta='';
                        $factura->servicios_renta='';
                    }
                    return $this->getOk($factura);
                }else{
                    return $this->getOk($fact);
                }



            } catch (\Exception $e) {
                return $this->insertErrCustom($input, $e->getMessage());
            }
    }




}
