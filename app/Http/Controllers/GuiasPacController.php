<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\SeriePac;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class GuiasPacController extends Controller
{
    use FormatResponseTrait;

    private $sqlgen="SELECT ALL SUBSTRING(NOCOMP03,1,7) AS agencia,
                            nocte31 AS codigocliente,
                            nomcte31 AS cliente,
                            codprod01 AS codigo,
	                        desprod01 AS articulo,
                            'FA' AS tipodoc,
	                        NOCOMP03 AS documento,
                            fecmov03 AS fecha,
	                        cantid03 AS cantidad,
                            (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca,
                            (SELECT nomtab FROM jcev.maetab WHERE numtab='73' AND codtab =novend31) AS vendedor,
                            numero_guia_remision
                    FROM xbase.movpro
                    INNER JOIN ybase.maepro ON codprod03 = codprod01
                    INNER JOIN zbase.maefac ON NOCOMP03=nofact31 AND cvanulado31!=9
                    LEFT JOIN xbase.guia_remision_electronica on numero_documento_origen=NOCOMP03
                    WHERE tipotra03 IN ('80') AND cvanulado03 <>'S' AND fecmov03 >= 'xfinicio'  AND fecmov03 <= 'xffin' AND  tipprod01='S' AND statuspro01='S'";


    private $sqlguias="SELECT ALL SUBSTRING(NOCOMP03,1,7) AS agencia,
                            nocte31 AS codigocliente,
                            nomcte31 AS cliente,
                            codprod01 AS codigo,
	                        desprod01 AS articulo,
                            'FA' AS tipodoc,
	                        NOCOMP03 AS documento,
                            date(fecha_emision) AS fecha,
	                        cantid03 AS cantidad,
                            (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca,
                            (SELECT nomtab FROM jcev.maetab WHERE numtab='73' AND codtab =novend31) AS vendedor,
                            numero_guia_remision,
                            ruc_transportista,
                            nombre_transportista
                    FROM xbase.movpro
                    INNER JOIN ybase.maepro ON codprod03 = codprod01
                    INNER JOIN zbase.maefac ON NOCOMP03=nofact31 AND cvanulado31!=9
                    LEFT JOIN xbase.guia_remision_electronica on numero_documento_origen=NOCOMP03
                    WHERE tipotra03 IN ('80') AND cvanulado03 <>'S' AND fecha_emision >= 'xfinicio'  AND fecha_emision <= 'xffin' AND  tipprod01='S' AND statuspro01='S'";

    public function __construct()
    {
        //$this->middleware('auth:admin');
    }


    public function ventaGuias(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $sql='';

        // select de guias
        $sql=$this->generaQueryGuias('jcev','jcev','jcev',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevcuenca2','jcevcuenca2','jcevcuenca2',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevcuenca1','jcevcuenca1','jcevcuenca1',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevgye1','jcevgye1','jcevgye1',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevgye10','jcevgye10','jcevgye10',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevuio1','jcevuio1','jcevuio1',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevconsigvirt','jcevuio1','jcevuio1',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevgyeassem','jcevgyeassem','jcevgyeassem',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevconsigvirt','jcevconsigvirt','jcevgyeassem',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevstecvir','jcevstecvir','jcevstecvir',$inicio,$fin);

        $sql='select codigocliente,
                    cliente,
                    documento,
                    fecha,
                    vendedor,
                    numero_guia_remision,
                    sum(cantidad) as cantidad
              from ('.$sql.' ) a group by codigocliente,cliente,documento,fecha,vendedor,numero_guia_remision';

        $list = DB::connection('mysqlpac')->select($sql);

        return $this->getOk($list);
    }


    public function ventaGuiasOptimus(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $sql='';

        // select de guias
        $sql=$this->generaQueryGuias('jcev','jcev','jcev',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevcuenca2','jcevcuenca2','jcevcuenca2',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevcuenca1','jcevcuenca1','jcevcuenca1',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevgye1','jcevgye1','jcevgye1',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevgye10','jcevgye10','jcevgye10',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevuio1','jcevuio1','jcevuio1',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevconsigvirt','jcevuio1','jcevuio1',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevgyeassem','jcevgyeassem','jcevgyeassem',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevconsigvirt','jcevconsigvirt','jcevgyeassem',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevstecvir','jcevstecvir','jcevstecvir',$inicio,$fin);

        $sql='select codigocliente,
                    cliente,
                    documento,
                    fecha,
                    vendedor,
                    numero_guia_remision,
                    sum(cantidad) as cantidad
              from ('.$sql.' ) a group by codigocliente,cliente,documento,fecha,vendedor,numero_guia_remision';

        $list = DB::connection('mysqlpac')->select($sql);

        return $this->getOk($list);
    }

    public function generaQueryGuias($bodega,$bodega1,$bodega2,$inicio,$fin)
    {
        $query=  str_replace('xbase',$bodega,$this->sqlgen);
        $query=  str_replace('ybase',$bodega1,$query);
        $query=  str_replace('zbase',$bodega2,$query);
        $query=  str_replace('xfinicio',$inicio,$query);
        $query=  str_replace('xffin',$fin,$query);
        return $query;
    }


    public function guiasDetallePac(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $sql='';

        // select de guias detalle
        $sql=$this->generaQueryGuiasDetalle('jcev','jcev','jcev',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuiasDetalle('jcevcuenca2','jcevcuenca2','jcevcuenca2',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuiasDetalle('jcevcuenca1','jcevcuenca1','jcevcuenca1',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuiasDetalle('jcevgye1','jcevgye1','jcevgye1',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuiasDetalle('jcevgye10','jcevgye10','jcevgye10',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuiasDetalle('jcevuio1','jcevuio1','jcevuio1',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuiasDetalle('jcevconsigvirt','jcevuio1','jcevuio1',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuiasDetalle('jcevgyeassem','jcevgyeassem','jcevgyeassem',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuiasDetalle('jcevconsigvirt','jcevconsigvirt','jcevgyeassem',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuiasDetalle('jcevstecvir','jcevstecvir','jcevstecvir',$inicio,$fin);

        /*$sql='select codigocliente,
                    cliente,
                    documento,
                    fecha,
                    vendedor,
                    numero_guia_remision,
                    sum(cantidad) as cantidad
              from ('.$sql.' ) a group by codigocliente,cliente,documento,fecha,vendedor,numero_guia_remision';
*/
        $list = DB::connection('mysqlpac')->select($sql);

        return $this->getOk($list);
    }

    public function guiasResumen(Request $request)
    {
        //fsigu cabmio guias detalle optimus
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $serie=isset($input['serie_id']) ?$input['serie_id']:'0';
        $sql='';

        //return $this->getOk($input);

        // select de guias detalle
        $sql="select doc.nombre as bodega,
                                    c.serie||'-'||lpad(c.numero,9,'0') as numero_guia_remision,
                                    c.fecha_inicio as fecha,
                                    c.ruc as codigocliente,
                                    c.cliente,
									dir.ciudad,
                                    dir.nombre as tienda,
                                    t.nombres as nombre_transportista,
                                    sum(d.cantidad) as items
                            from guias_remision c
                            inner join guias_remisiond d on c.id=d.guiar_id
                            inner join transportistas t on c.transportista_id=t.id
                            left join direcciones dir on c.direccion_id=dir.id
                            left join documentos doc on c.serie=doc.serie and doc.codigo='GR'
                            where fecha_inicio>=? and fecha_inicio<=?
                                and case when '0'=? then true else c.serie=? end
							group by 1,2,3,4,5,6,7,8
							order by 1,3,2,5";
        $list = DB::select($sql,[$inicio,$fin,$serie,$serie]);
        return $this->getOk($list);
    }


    public function guiasDetalle(Request $request)
    {
        //fsigu cabmio guias detalle optimus
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $serie=isset($input['serie_id']) ?$input['serie_id']:'0';
        $sql='';

        //return $this->getOk($input);

        // select de guias detalle
        $sql="select doc.nombre as bodega,
                                    c.serie||'-'||lpad(c.numero,9,'0') as numero_guia_remision,
                                    c.fecha_inicio as fecha,
                                    c.observacion,
                                    c.ruc as codigocliente,
                                    c.cliente,
                                    c.transportista_id,
                                    c.placa,
                                    c.ruta,
									dir.ciudad,
                                    dir.nombre as tienda,
                                    t.ruc as ruc_transportista,
                                    t.nombres as nombre_transportista,
                                    '' as vendedor,
                                    '' as marca,
                                    d.codigo,
                                    d.descripcion as articulo,
                                    d.serie,
                                    d.cantidad
                            from guias_remision c
                            inner join guias_remisiond d on c.id=d.guiar_id
                            inner join transportistas t on c.transportista_id=t.id
                            left join direcciones dir on c.direccion_id=dir.id
                            left join documentos doc on c.serie=doc.serie and doc.codigo='GR'
                            where fecha_inicio>=? and fecha_inicio<=?
                                and case when '0'=? then true else c.serie=? end";

        $list = DB::select($sql,[$inicio,$fin,$serie,$serie]);

        return $this->getOk($list);
    }


    public function generaQueryGuiasDetalle($bodega,$bodega1,$bodega2,$inicio,$fin)
    {
        $query=  str_replace('xbase',$bodega,$this->sqlguias);
        $query=  str_replace('ybase',$bodega1,$query);
        $query=  str_replace('zbase',$bodega2,$query);
        $query=  str_replace('xfinicio',$inicio,$query);
        $query=  str_replace('xffin',$fin,$query);
        return $query;
    }




}
