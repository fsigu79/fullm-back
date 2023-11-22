<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\GuiaRemision;
use App\Models\GuiaRemisionDetalle;
use Illuminate\Http\Request;
use App\Models\SqlModel;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class GuiasProductosController extends Controller
{
    use FormatResponseTrait;

    private $sqlgenDes="select distinct if (tipo='02','FACTURA','TRANSFERENCIA') as tipo,
                            numeroFactura,
                            cliente as codigoCliente,
                            fechaFactura,
                            fechaDespacho,
                            c.nomcte01 as nombreCliente,
                            c.cascte01 as rucCliente,
                            dd.direccion_despacho as direccion,
                            if (d.estado=1,'DESPACHADA','PENDIENTE') as estado
                    from xbase.despacho_facturas d
                    inner join jcev.maecte c on d.cliente=c.codcte01
                    inner join xbase.despacho_facturas_productos dd on d.numeroFactura=dd.numero_factura
                    where dd.estado=0 ";

    private $sqlgen="Select serie04 as serie,tipotra04 as tipo_transaccion,nocomp04 as documento1,
                        codprod04 as codigo,desprod01 as descripcion,
                        chasis04 as chasis,coddest04 as destino,valor04 as valor,
                        fecmov04 as fecha,
                        (select max(fecmov05) from jcevgyeassem.hisser where  serie05=serie04 and tipotra05=80) as fecha1,
                        (SELECT desccate FROM jcev.categorias WHERE tipocate='02' and codcate = catprod01) as categoria,
                        anio04 as anio,
                        color04 as color,
                        cpn04 as cpn,
                        ramv04,
                        cvanulada04,
                        if (cvanulada04='A','ANULADA', 'ACTIVO') as estado,
                        nofact04 as numero_factura_pac,
                        nopedido04 as pedido,
                        catprod01,
                        notransfer04,
                        'cod' as cliente_codigo,
                        'cli' as cliente,
                        if (cvanulada04='F',(select concat('FAC-',nocomp05) from jcevgyeassem.hisser where serie05=serie04 and tipotra05=80 and fecmov05=(select max(fecmov05) from jcevgyeassem.hisser where  serie05=serie04 and tipotra05=80 )),
                           (select concat('TRA-',nocomp05) from jcevgyeassem.hisser where serie05=serie04 and tipotra05=61 and fecmov05=(select max(fecmov05) from jcevgyeassem.hisser where  serie05=serie04 and tipotra05=61))) as documento
                    from jcevgyeassem.maeser
                    inner join jcevgyeassem.maepro on codprod04=codprod01
                    left join jcevgyeassem.maefac on jcevgyeassem.maefac.nofact31=jcevgyeassem.maeser.nofact04
                    where  fecmov04>=? and fecmov04<=? and (cvanulada04 in ('T'))
                    order by documento ASC";



        private $sqlgen1="Select serie04 as serie,tipotra04 as tipo_transaccion,nocomp04 as documento1,
                        codprod04 as codigo,desprod01 as descripcion,
                        chasis04 as chasis,coddest04 as destino,valor04 as valor,
                        fecmov04 as fecha1,
                        (select max(fecmov05) from jcevgyeassem.hisser where  serie05=serie04 and tipotra05=80) as fecha,
                        (SELECT desccate FROM jcev.categorias WHERE tipocate='02' and codcate = catprod01) as categoria,
                        anio04 as anio,
                        color04 as color,
                        cpn04 as cpn,
                        ramv04,
                        cvanulada04,
                        if (cvanulada04='A','ANULADA', 'ACTIVO') as estado,
                        nofact04 as numero_factura_pac,
                        nopedido04 as pedido,
                        catprod01,
                        notransfer04,
                        nocte31 as cliente_codigo,
                        nomcte31 as cliente,
                        if (cvanulada04='F',(select concat('FAC-',nocomp05) from jcevgyeassem.hisser where serie05=serie04 and tipotra05=80 and fecmov05=(select max(fecmov05) from jcevgyeassem.hisser where  serie05=serie04 and tipotra05=80 )),
                           (select concat('TRA-',nocomp05) from jcevgyeassem.hisser where serie05=serie04 and tipotra05=61 and fecmov05=(select max(fecmov05) from jcevgyeassem.hisser where  serie05=serie04 and tipotra05=61))) as documento
                    from jcevgyeassem.maeser
                    inner join jcevgyeassem.maepro on codprod04=codprod01
                    left join jcevgyeassem.maefac on jcevgyeassem.maefac.nofact31=jcevgyeassem.maeser.nofact04
                    where  fecfact31>=? and fecfact31<=? and (cvanulada04 in ('F'))
                    union all
                     Select serie04 as serie,tipotra04 as tipo_transaccion,nocomp04 as documento1,
                        codprod04 as codigo,desprod01 as descripcion,
                        chasis04 as chasis,coddest04 as destino,valor04 as valor,
                        fecmov04 as fecha,
                        (select max(fecmov05) from jcevgyeassem.hisser where  serie05=serie04 and tipotra05=80) as fecha1,
                        (SELECT desccate FROM jcev.categorias WHERE tipocate='02' and codcate = catprod01) as categoria,
                        anio04 as anio,
                        color04 as color,
                        cpn04 as cpn,
                        ramv04,
                        cvanulada04,
                        if (cvanulada04='A','ANULADA', 'ACTIVO') as estado,
                        nofact04 as numero_factura_pac,
                        nopedido04 as pedido,
                        catprod01,
                        notransfer04,
                        'cod' as cliente_codigo,
                        'cli' as cliente,
                        if (cvanulada04='F',(select concat('FAC-',nocomp05) from jcevgyeassem.hisser where serie05=serie04 and tipotra05=80 and fecmov05=(select max(fecmov05) from jcevgyeassem.hisser where  serie05=serie04 and tipotra05=80 )),
                           (select concat('TRA-',nocomp05) from jcevgyeassem.hisser where serie05=serie04 and tipotra05=61 and fecmov05=(select max(fecmov05) from jcevgyeassem.hisser where  serie05=serie04 and tipotra05=61))) as documento
                    from jcevgyeassem.maeser
                    inner join jcevgyeassem.maepro on codprod04=codprod01
                    left join jcevgyeassem.maefac on jcevgyeassem.maefac.nofact31=jcevgyeassem.maeser.nofact04
                    where  fecmov04>=? and fecmov04<=? and (cvanulada04 in ('T'))
                    order by documento ASC";

            //fecfact31 antes fecha fecmov04
    public function __construct()
    {
        $this->middleware('auth:admin');
    }



    public function importaCatalogoSeries(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $usuario=Auth::user();
        try{
            $list = DB::connection('mysqlpac')->select($this->sqlgen,[$inicio,$fin,$inicio,$fin]);

            foreach ($list as $detalle) {

                /*if (is_null($detalle->estado)) {
                    $detalle->estado='0';
                }
                if (is_null($detalle->pedido)) {
                    $detalle->pedido='.';
                }
                if (is_null($detalle->notransfer04)) {
                    $detalle->notransfer04='.';
                }
                if (is_null($detalle->cliente_codigo)) {
                    $detalle->cliente_codigo='.';
                }
                if (is_null($detalle->cliente)) {
                    $detalle->cliente='.';
                }*/
                $results=DB::select('SELECT catalogo_series_grabar(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',[
                                $detalle->serie,
                                $detalle->tipo_transaccion,
                                $detalle->documento,
                                $detalle->codigo,
                                $detalle->descripcion,
                                $detalle->chasis,
                                $detalle->destino,
                                $detalle->valor,
                                $detalle->fecha,
                                $detalle->categoria,
                                $detalle->anio,
                                $detalle->color,
                                $detalle->cpn,
                                $detalle->ramv04,
                                $detalle->cvanulada04,
                                $detalle->estado,
                                $detalle->numero_factura_pac,
                                $detalle->pedido,
                                $detalle->catprod01,
                                $detalle->notransfer04,
                                $detalle->cliente_codigo,
                                $detalle->cliente,
                                $usuario->id,
                                ]);

            }
            $catalogo=DB::select('SELECT * from catalogo_series');
            //guia_remision_numero
            //guia_remision_id
            return $this->getOk($catalogo);

         } catch (\Exception $e) {
            return $this->insertErrCustom($request, $e->getMessage());
        }
    }



    public function obtenerCatalogoSeries(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        try{
            $catalogo=DB::select('SELECT * from catalogo_series');
            return $this->getOk($catalogo);

        } catch (\Exception $e) {
            return $this->insertErrCustom($request, $e->getMessage());
        }
    }



    public function importaDespachos(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $usuario=Auth::user();
        try{

             // select de guias
            $sql = $this->generaQueryDespachos('jcev');
           $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('jcevcuenca2');
            $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('jcevcuenca1');
            $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('jcevgye1');
            $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('jcevgye3');
            $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('jcevgye10');
            $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('jcevuio1');
            $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('jcevconsigvirt');
            $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('jcevgyeassem');
            $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('jcevconsigvirt');
            $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('jcevstecvir');
            $sql = $sql . 'order by numeroFactura,fechaFactura desc ';


             /* $box = new SqlModel();
            $box->sql= $sql;
            $box->sql1='ok';
            $box->save();*/


            $list = DB::connection('mysqlpac')->select($sql);

            return $this->getOk($list);

         } catch (\Exception $e) {
            return $this->insertErrCustom($request, $e->getMessage());
        }
    }

    public function generaQueryDespachos($bodega)
    {
        $query =  str_replace('xbase', $bodega, $this->sqlgenDes);
        //$query =  str_replace('xfinicio', $inicio, $query);
        //$query =  str_replace('xffin', $fin, $query);
        return $query;
    }

    public function importaDespachosDetalle(Request $request)
    {
        $input = $request->all();
        $factura=$request['factura'];
        $direccion=$request['direccion'];
        $serie=substr($factura,0,7);
        $base='jcev';

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
                } else {
                    $base='jcev';
                }

        $usuario=Auth::user();
        try{

             // select de guias
            $sql = 'select id_despacho,numero_factura,codigo_cliente,codigo_producto as codigo,
                    cantidad_despachada as cantidad,dispone_serie,numero_serie as serie,
                    direccion_despacho as aliasDireccion,
                    desprod01 as descripcion,
                    "" as chasis,"" as color,"" as anio
                    from xbase.despacho_facturas_productos
                    inner join jcev.maepro on codigo_producto=codprod01
                    where numero_factura=? and  direccion_despacho=?';

            $query =  str_replace('xbase', $base, $sql);

            /*$box = new SqlModel();
            $box->sql= $serie;
            $box->sql1=$query;
            $box->save();*/



            $list = DB::connection('mysqlpac')->select($query,[$factura,$direccion]);

            return $this->getOk($list);

         } catch (\Exception $e) {
            return $this->insertErrCustom($request, $e->getMessage());
        }
    }

    public function obtenerClienteByCodigo(Request $request)
    {
        $input = $request->all();
        $factura=$request['factura'];

        $usuario=Auth::user();
        try{

             // select de guias
            $sql = 'select * from maecte where codcte01=?';

            $list = DB::connection('mysqlpac')->select($sql,[$factura]);

            return $this->getOk($list);

         } catch (\Exception $e) {
            return $this->insertErrCustom($request, $e->getMessage());
        }
    }


    public function actualizaEstadoFacturas(Request $request)
    {
        $input = $request->all();
        $factura=$request['factura'];
        $direccion=$request['direccion'];

        $usuario=Auth::user();
        try{

             // select de guias
            //$sql = 'update despacho_facturas set estado=0 where numeroFactura=?';

            //$list = DB::connection('mysqlpac')->select($sql,[$factura]);

            $sql = 'update despacho_facturas_productos set estado=1 where numero_factura=? and direccion_despacho=?';
            $list = DB::connection('mysqlpac')->update($sql,[$factura,$direccion]);

            return $this->getOk($list);

         } catch (\Exception $e) {
            return $this->insertErrCustom($request, $e->getMessage());
        }
    }



}
