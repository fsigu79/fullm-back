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
use Carbon\Carbon;

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
                    inner join fullm.maecte c on d.cliente=c.codcte01
                    inner join xbase.despacho_facturas_productos dd on d.numeroFactura=dd.numero_factura
                    where dd.estado=0 ";

    private $sqlgen="Select serie04 as serie,tipotra04 as tipo_transaccion,nocomp04 as documento1,
                        codprod04 as codigo,desprod01 as descripcion,
                        chasis04 as chasis,coddest04 as destino,valor04 as valor,
                        fecmov04 as fecha,
                        (select max(fecmov05) from fullmgyeassem.hisser where  serie05=serie04 and tipotra05=80) as fecha1,
                        (SELECT desccate FROM fullm.categorias WHERE tipocate='02' and codcate = catprod01) as categoria,
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
                        if (cvanulada04='F',(select concat('FAC-',nocomp05) from fullmgyeassem.hisser where serie05=serie04 and tipotra05=80 and fecmov05=(select max(fecmov05) from fullmgyeassem.hisser where  serie05=serie04 and tipotra05=80 )),
                           (select concat('TRA-',nocomp05) from fullmgyeassem.hisser where serie05=serie04 and tipotra05=61 and fecmov05=(select max(fecmov05) from fullmgyeassem.hisser where  serie05=serie04 and tipotra05=61))) as documento
                    from fullmgyeassem.maeser
                    inner join fullmgyeassem.maepro on codprod04=codprod01
                    left join fullmgyeassem.maefac on fullmgyeassem.maefac.nofact31=fullmgyeassem.maeser.nofact04
                    where  fecmov04>=? and fecmov04<=? and (cvanulada04 in ('T'))
                    order by documento ASC";



        private $sqlgen1="Select serie04 as serie,tipotra04 as tipo_transaccion,nocomp04 as documento1,
                        codprod04 as codigo,desprod01 as descripcion,
                        chasis04 as chasis,coddest04 as destino,valor04 as valor,
                        (select max(fecmov05) from fullmgyeassem.hisser where  serie05=serie04 and tipotra05=80) as fecha,
                        fecmov04 as fecha1,
                        (SELECT desccate FROM fullm.categorias WHERE tipocate='02' and codcate = catprod01) as categoria,
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
                        if (cvanulada04='F',(select concat('FAC-',nocomp05) from fullmgyeassem.hisser where serie05=serie04 and tipotra05=80 and fecmov05=(select max(fecmov05) from fullmgyeassem.hisser where  serie05=serie04 and tipotra05=80 )),
                           (select concat('TRA-',nocomp05) from fullmgyeassem.hisser where serie05=serie04 and tipotra05=61 and fecmov05=(select max(fecmov05) from fullmgyeassem.hisser where  serie05=serie04 and tipotra05=61))) as documento
                    from fullmgyeassem.maeser
                    inner join fullmgyeassem.maepro on codprod04=codprod01
                    left join fullmgyeassem.maefac on fullmgyeassem.maefac.nofact31=fullmgyeassem.maeser.nofact04
                    where  fecfact31>=? and fecfact31<=? and (cvanulada04 in ('F'))
                    union all
                     Select serie04 as serie,tipotra04 as tipo_transaccion,nocomp04 as documento1,
                        codprod04 as codigo,desprod01 as descripcion,
                        chasis04 as chasis,coddest04 as destino,valor04 as valor,
                        fecmov04 as fecha,
                        (select max(fecmov05) from fullmgyeassem.hisser where  serie05=serie04 and tipotra05=11) as fecha1,
                        (SELECT desccate FROM fullm.categorias WHERE tipocate='02' and codcate = catprod01) as categoria,
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
                        if (cvanulada04='F',(select concat('FAC-',nocomp05) from fullmgyeassem.hisser where serie05=serie04 and tipotra05=80 and fecmov05=(select max(fecmov05) from fullmgyeassem.hisser where  serie05=serie04 and tipotra05=80 )),
                           (select concat('TRA-',nocomp05) from fullmgyeassem.hisser where serie05=serie04 and tipotra05=61 and fecmov05=(select max(fecmov05) from fullmgyeassem.hisser where  serie05=serie04 and tipotra05=61))) as documento
                    from fullmgyeassem.maeser
                    inner join fullmgyeassem.maepro on codprod04=codprod01
                    left join fullmgyeassem.maefac on fullmgyeassem.maefac.nofact31=fullmgyeassem.maeser.nofact04
                    where  fecmov04>=? and fecmov04<=? and (cvanulada04 in ('T'))
                    union all

                     Select serie04 as serie,tipotra04 as tipo_transaccion,nocomp04 as documento1,
                        codprod04 as codigo,desprod01 as descripcion,
                        chasis04 as chasis,coddest04 as destino,valor04 as valor,
                        fecmov04 as fecha,
                        (select max(fecmov05) from fullmgyeassem.hisser where  serie05=serie04 and tipotra05=80) as fecha1,
                        (SELECT desccate FROM fullm.categorias WHERE tipocate='02' and codcate = catprod01) as categoria,
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
                        codcte30 as cliente_codigo,
                        nomcte30 as cliente,
                        if (cvanulada04='P',(select concat('PED-',nocomp05) from fullmgyeassem.hisser where serie05=serie04 and tipotra05=11 and fecmov05=(select max(fecmov05) from fullmgyeassem.hisser where  serie05=serie04 and tipotra05=11 )),'') as documento
                    from fullmgyeassem.maeser
                    inner join fullmgyeassem.maepro on codprod04=codprod01
                    left join fullmgyeassem.maeped30 on fullmgyeassem.maeped30.nopedido30=fullmgyeassem.maeser.nopedido04 and fullmgyeassem.maeser.codprod04=fullmgyeassem.maeped30.codprod30
                    where  fecmov04>=? and fecmov04<=? and (cvanulada04 in ('P'))
                    order by documento ASC";



            private $sqlTransferncias="select serie05 serie,
                           fecmov05 as fecha,
                           concat('TRA-',nocomp05) as documento,
                           if (cvanulada04='A','ANULADA', 'ACTIVO') as estado,
                           tipotra04 as tipo_transaccion,
                           nofact04 as numero_factura_pac,
                           nocomp04 as documento1,
                           nopedido04 as pedido,
                           codprod05 as codigo,
                           desprod01 as descripcion,
                           chasis04 as chasis,
                           coddest04 as destino,
                           valor04 as valor,
                           catprod01,
                           (SELECT desccate FROM fullm.categorias WHERE tipocate='02' and codcate = catprod01) as categoria,
                           anio04 as anio,
                           color04 as color,
                           cpn04 as cpn,
                           ramv04,
                           cvanulada04,
                           notransfer04,
                           codcte30 as cliente_codigo,
                           nomcte30 as cliente
                    from (
                      select serie05 ,fecmov05,nocomp05,codprod05
                      from ybase.hisser
                      where nocomp05 in (select distinct nocomp05 from ybase.hisser where fecmov05>='xfinicio' and  fecmov05<='xfin' and tipotra05='61')
                      and tipotra05='61' ) as his
                    inner join xbase.maepro on his.codprod05=codprod01
                    inner join ybase.maeser on his.serie05 = ybase.maeser.serie04
                    left join xbase.maeped30 on nopedido30=nopedido04
                    union all
                    select serie05 serie,
                           fecmov05 as fecha,
                           concat('FAC-',nocomp05) as documento,
                           if (cvanulada04='A','ANULADA', 'ACTIVO') as estado,
                           tipotra04 as tipo_transaccion,
                           nofact04 as numero_factura_pac,
                           nocomp04 as documento1,
                           nopedido04 as pedido,
                           codprod05 as codigo,
                           desprod01 as descripcion,
                           chasis04 as chasis,
                           coddest04 as destino,
                           valor04 as valor,
                           catprod01,
                           (SELECT desccate FROM fullm.categorias WHERE tipocate='02' and codcate = catprod01) as categoria,
                           anio04 as anio,
                           color04 as color,
                           cpn04 as cpn,
                           ramv04,
                           cvanulada04,
                           notransfer04,
                           nocte31 as cliente_codigo,
                           nomcte31 as cliente
                    from (
                      select serie05 ,fecmov05,nocomp05,codprod05
                      from ybase.hisser
                      where nocomp05 in (select distinct nocomp05 from ybase.hisser where fecmov05>='xfinicio' and  fecmov05<='xfin' and tipotra05='80')
                      and tipotra05='80' ) as his
                    inner join xbase.maepro on his.codprod05=codprod01
                    inner join ybase.maeser on his.serie05 = ybase.maeser.serie04
                    left join  xbase.maefac on xbase.maefac.nofact31=his.nocomp05
                    where xbase.maefac.cvanulado31!=9";

            //fecfact31 antes fecha fecmov04
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    //
    //Series pac , metodo que importa las series del pac si es transferencia o factura
    //
    public function importaCatalogoSeries(Request $request)
    {
        $input = $request->all();
        //$inicio=$request['finicio'].' 00:00:00';
        $inicio=$request['ffin'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';

        $fecha=Carbon::parse($fin);
        $fechaini=$fecha->subDays(3);
        $fechaini=$fechaini->format('Y-m-d').' 00:00:00';

        $usuario=Auth::user();
        try{
            $sql=$this->generaQuery('fullmgyeassem','fullmgyeassem',$fechaini,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('fullmgyeassem','fullmcuenca1',$fechaini,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('fullmgyeassem','fullmconsigvirt',$fechaini,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('fullmgyeassem2','fullmgyeassem2',$fechaini,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('fullmgye3','fullmgye3',$fechaini,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('fullmgye1','fullmgye1',$fechaini,$fin);

            //$list = DB::connection('mysqlpac')->select($this->sqlTransferncias,[$inicio,$fin,$inicio,$fin,$inicio,$fin]);

            //fsigu sqls
            /*
            $box = new SqlModel();
            $box->sql= $sql;
            $box->sql1=$sql;
            $box->save();
            */

            $list = DB::connection('mysqlpac')->select($sql);
            //return $this->getOk($list);


            foreach ($list as $detalle) {

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
            $catalogo=DB::select('SELECT * from catalogo_series where fecha is null or (fecha>=? and fecha<=?)',[$fechaini,$fin]);
            //guia_remision_numero
            //guia_remision_id
            return $this->getOk($catalogo);

         } catch (\Exception $e) {
            return $this->insertErrCustom($request, $e->getMessage());
        }
    }

    public function generaQuery($bodegam,$bodegap,$inicio,$fin)
    {
        $query=  str_replace('xbase',$bodegam,$this->sqlTransferncias);
        $query=  str_replace('ybase',$bodegap,$query);
        $query=  str_replace('xfinicio',$inicio,$query);
        $query=  str_replace('xfin',$fin,$query);

        return $query;
    }



    public function obtenerCatalogoSeries(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $fecha=Carbon::parse($fin);
        $fechaini=$fecha->subDays(20);
        $fechaini=$fechaini->format('Y-m-d').' 00:00:00';

        try{
            $catalogo=DB::select('SELECT * from catalogo_series where fecha is null or (fecha>=? and fecha<=?) order by fecha desc, documento desc',[$fechaini,$fin]);
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
            $sql = $this->generaQueryDespachos('fullm');
           $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('fullmcuenca2');
            $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('fullmcuenca1');
            $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('fullmgye1');
            $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('fullmgye10');
            $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('fullmuio1');
            $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('fullmconsigvirt');
            $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('fullmgyeassem');
            $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('fullmgyeassem2');
            $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('fullmconsigvirt');
            $sql = $sql . ' UNION ALL ' . $this->generaQueryDespachos('fullmstecvir');
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
        $base='fullm';

        if ($serie=='001-100'){
                    $base='fullm';
                } else if ($serie=='001-102') {
                    $base='fullmgye1';
                } else if ($serie=='001-103') {
                    $base='fullmgyeassem';
                } else if ($serie=='001-104') {
                    $base='fullmuio1';
                } else if ($serie=='001-105') {
                    $base='fullmcuenca2';
                } else if ($serie=='001-106') {
                    $base='fullmcuenca1';
                } else if ($serie=='001-114') {
                    $base='fullmgye3';
                } else if ($serie=='001-107') {
                    $base='fullmgye10';
                } else if ($serie=='001-108') {
                    $base='fullmstecvir';
                } else if ($serie=='001-117') {
                    $base='fullmgyeassem2';
                } else {
                    $base='fullm';
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
                    inner join fullm.maepro on codigo_producto=codprod01
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
