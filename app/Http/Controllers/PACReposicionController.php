<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\ReporteTransito;
use App\Models\SqlModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class PACReposicionController extends Controller
{
    use FormatResponseTrait;

    private $sqlgen="SELECT ALL
					        codprod01 AS codigo,
	                        desprod01 AS articulo,
	                        NOCOMP03 AS documento,
                            fecmov03 AS fecha,
                            cantid03 AS cantidad,
	                        marca01 AS marcod,
  					        (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca,
					        catcte01 AS catcod,
  					        (SELECT b.desccate AS categoria FROM jcev.categorias a INNER JOIN  jcev.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate
               FROM xbase.movpro
               INNER JOIN ybase1.maepro ON codprod03 = codprod01
               INNER JOIN zbase1.maefac ON NOCOMP03=nofact31 AND cvanulado31!=9
               INNER JOIN jcev.maecte ON nocte31=codcte01
               WHERE tipotra03 IN ('80') AND cvanulado03 <>'S' AND fecmov03 >= 'xfinicio'  AND fecmov03 <= 'xffin' AND  tipprod01='S'
                    and case when '0'='xmarc' then true else marca01 in ('xmarc') end
                    and case when '0'='xprod' then true else codprod01 in ('xprod') end
                    and case when '0'='xclie' then true else nocte31 in ('xclie') end";

    private $sqlgennc="SELECT IFNULL(codprod01,'-') AS codigo,
						        desprod01 AS articulo,
						        numdoc43 AS documento,
                                fecdoc43 AS fecha,
                                IFNULL(cantid03*-1,0) AS cantidad,
						        marca01 AS marcod,
						        (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca,
						        catcte01 AS catcod,
						        (SELECT b.desccate AS categoria FROM jcev.categorias a INNER JOIN  jcev.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate
					      FROM jcev.movcte
					      INNER JOIN jcev.maecte ON codcte43=codcte01
					      INNER JOIN xbase.movpro  ON NOCOMP03=numdoc43 AND tipotra03 IN ('22') AND cvanulado03 <>'S'
					      INNER JOIN xbase.maepro ON codprod03 = codprod01 AND  tipprod01='S'  AND statuspro01='S'
					      WHERE tipodoc43 IN ('53')  AND fecdoc43 >= 'xfinicio'  AND fecdoc43 <= 'xffin' AND cvanulado43<>'S' AND tipoNC43='P' and ocurren43 in ('00','0000')
                                and case when '0'='xmarc' then true else marca01 in ('xmarc') end
                                and case when '0'='xprod' then true else codprod01 in ('xprod') end
                                and case when '0'='xclie' then true else codcte43 in ('xclie') end";


    private $sqlgennc1="SELECT IFNULL(codprod01,'-') AS codigo,
						        desprod01 AS articulo,
						        numdoc43 AS documento,
                                fecdoc43 AS fecha,
						        IFNULL(cantid03*-1,0) AS cantidad,
						        marca01 AS marcod,
						        (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca,
						        catcte01 AS catcod,
						        (SELECT b.desccate AS categoria FROM jcev.categorias a INNER JOIN  jcev.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate
					      FROM jcev.movcte2
					      INNER JOIN jcev.maecte ON codcte43=codcte01
					      INNER JOIN xbase.movpro  ON NOCOMP03=numdoc43 AND tipotra03 IN ('22') AND cvanulado03 <>'S'
					      INNER JOIN xbase.maepro ON codprod03 = codprod01 AND  tipprod01='S'  AND statuspro01='S'
					      WHERE tipodoc43 IN ('53')  AND fecdoc43 >= 'xfinicio'  AND fecdoc43 <= 'xffin' AND cvanulado43<>'S' AND tipoNC43='P'  and ocurren43 in ('00','0000')
                                and case when '0'='xmarc' then true else marca01 in ('xmarc') end
                                and case when '0'='xprod' then true else codprod01 in ('xprod') end
                                and case when '0'='xclie' then true else codcte43 in ('xclie') end";



    public function __construct()
    {
        //$this->middleware('auth:admin');
    }




    public function reposicionProducto(Request $request)
    {
        $input = $request->all();
        $bodega_filtro='';

        $fechai=Carbon::parse($input['ffin']);
        //$inicio = Carbon::create($fechai->year,$fechai->month, 1, 0, 0, 0);
        $inicio = $fechai->subMonths(3);
        $inicio = Carbon::create($inicio->year,$inicio->month, 1, 0, 0, 0);
        $inicio=$inicio->format('Y-m-d').' 00:00:00';
        //return $this->getOk($inicio);
        $mes1=Carbon::parse($input['ffin'])->subMonths(1)->month;
        $mes2=Carbon::parse($input['ffin'])->subMonths(2)->month;-
        $mes3=Carbon::parse($input['ffin'])->subMonths(3)->month;

        $fin=Carbon::parse($input['ffin'])->subMonths(1)->lastOfMonth();
        $fin=$fin->format('Y-m-d').' 23:59:00';
        //return $this->getOk($fin);

        //consultar si la fecha fin se cosidera los dias en los ue se encuentre o solo el final de los 3 meses completos
        //$fin=$request['ffin'].' 23:59:00';

        $marca=isset($request['marca_id']) ?$request['marca_id']:'0';
        $producto=isset($request['producto_id']) ?$request['producto_id']:'0';
        $cliente=isset($request['cliente_id']) ?$request['cliente_id']:'0';
        $vendedor='';

        //return $this->getOk($inicio);

        $sql='';
        $sqlnc='';

                // select de ventas
                $sql=$this->generaQueryVentas('jcev',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                //$sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevcuenca2',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                //$sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevcuenca1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                // $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevgye1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                // $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevgye10',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                // $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevuio1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                // $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevconsigvirt',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                // $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevgyeassem',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                // $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevconsigvirt',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'SI');
                // $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevstecvir',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                // // select de notas de credito
                $sqlnc=$this->generaQueryNC('jcev',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                // $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevconsigvirt',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                // $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevcuenca2',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                // //$sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevcuenca1',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                // $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevgye1',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                // $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevgye10',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                // $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevuio1',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                // $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevgyeassem',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                // $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevstecvir',$inicio,$fin,$marca,$producto,$vendedor,$cliente);

                // IFNULL((SELECT cantact01 FROM jcevcuenca2.maepro WHERE codigo=jcevcuenca2.maepro.codprod01),0)+
                // IFNULL((SELECT cantact01 FROM jcevgye1.maepro WHERE codigo=jcevgye1.maepro.codprod01),0)+
                // IFNULL((SELECT cantact01 FROM jcevuio1.maepro WHERE codigo=jcevuio1.maepro.codprod01),0)+
                // IFNULL((SELECT cantact01 FROM jcevconsigvirt.maepro WHERE codigo=jcevconsigvirt.maepro.codprod01),0) +
                // IFNULL((SELECT cantact01 FROM jcevstecvir.maepro WHERE codigo=jcevstecvir.maepro.codprod01),0) +
                // IFNULL((SELECT cantact01 FROM jcevgyeassem.maepro WHERE codigo=jcevgyeassem.maepro.codprod01),0) +
                // IFNULL((SELECT cantact01 FROM jcevcuenca1.maepro WHERE codigo=jcevcuenca1.maepro.codprod01),0) +
                // IFNULL((SELECT cantact01 FROM jcevgye10.maepro WHERE codigo=jcevgye10.maepro.codprod01),0)


            $sql='SELECT 	codigo,
                                articulo,
                                marca,
                                (
                                    IFNULL((SELECT cantact01 FROM jcev.maepro WHERE codigo=jcev.maepro.codprod01),0)
                                    
                                ) as stock,
					            ROUND(SUM(IF(MONTH(fecha) = '.$mes3.',  cantidad, 0)),2) AS mes3,
								ROUND(SUM(IF(MONTH(fecha) = '.$mes2.',  cantidad, 0)),2) AS mes2,
								ROUND(SUM(IF(MONTH(fecha) = '.$mes1.',  cantidad, 0)),2) AS mes1,
								ROUND(SUM(cantidad),2) AS total
                            FROM
                                 (  '.$sql.' UNION ALL SELECT codigo, articulo,
	                            documento,fecha,cantidad,
	                            marcod,marca,
					            catcod,cate
                        FROM ( '.$sqlnc.') a'.') b
                                      GROUP BY b.codigo,b.articulo,b.marca
                                      ORDER BY SUM(b.cantidad) DESC';


        //echo $sql;
        //fsigu sqls
        /* $box = new SqlModel();
            $box->sql= $sql;
            $box->sql1=$sql;
            $box->save();*/
        //return $this->getOk($sql);


        $list = DB::connection('mysqlpac')->select($sql);

        //elimina datos de la tabla tmporal
        $elimina = DB::select("delete from reporte_reposicion");
        foreach ($list as $detalle) {
            $results=DB::select('SELECT reporte_reposicion_grabar(?,?,?,?,?,?,?,?,?)',[
                            $detalle->codigo,
                            $detalle->articulo,
                            $detalle->marca,
                            '',
                            $detalle->stock,
                            $detalle->mes3,
                            $detalle->mes2,
                            $detalle->mes1,
                            $detalle->total
                            ]);
        };

        /*foreach ($list as $detalle) {
            $entidad= new ReporteTransito();
            $entidad->codigo =$detalle->codigo;
            $entidad->articulo = $detalle->articulo;
            $entidad->marca =$detalle->marca;
            $entidad->categoria = '';
            $entidad->stock = $detalle->stock;
            $entidad->mes3 = $detalle->mes3;
            $entidad->mes2 = $detalle->mes2;
            $entidad->mes1 = $detalle->mes1;
            $entidad->total = $detalle->total;
            $entidad->save();
        };*/


        $results=DB::select('SELECT * from reporte_reposicion(?,?)',[ 'marca',
                            'categoria'
                            ]);
        return $this->getOk($results);
    }


    public function ventamesmarca(Request $request)
    {
        $input = $request->all();
        $bodega = isset($request['marca_id']) ?$request['bodega_id']:'todas';
        $bodega_filtro='';
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $marca=isset($request['marca_id']) ?$request['marca_id']:'0';
        $producto=isset($request['producto_id']) ?$request['producto_id']:'0';
        $vendedor=isset($request['vendedor_id']) ?$request['vendedor_id']:'0';
        $cliente=isset($request['cliente_id']) ?$request['cliente_id']:'0';
        $unidades=isset($request['unidades']) ?$request['unidades']:false;
        $sql='';
        $sqlnc='';



        if ($bodega=='todas'){
                // select de ventas
                $sql=$this->generaQueryVentas('jcev',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevcuenca2',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevcuenca1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevgye1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevgye10',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevuio1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevconsigvirt',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevgyeassem',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevconsigvirt',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'SI');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevstecvir',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                // select de notas de credito
                $sqlnc=$this->generaQueryNC('jcev',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevconsigvirt',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevcuenca2',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                //$sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevcuenca1',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevgye1',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevgye10',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevuio1',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevgyeassem',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevstecvir',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                //$sqlnc=$sqlnc.' UNION '.$this->generaQueryNCMatriz('jcev',$inicio,$fin,$marca,$producto,$vendedor,$cliente);

        }
        else
        {
            // select de ventas
            $sql=$this->generaQueryVentas($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
            // select de notas de credito
            $sqlnc=$this->generaQueryNC($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente);
        }

        if ($unidades=="true")
        {
            $sql='SELECT 	marca,
					            ROUND(SUM(IF(MONTH(fecha) = 1,  cantidad, 0)),2) AS ene,
								ROUND(SUM(IF(MONTH(fecha) = 2,  cantidad, 0)),2) AS feb,
								ROUND(SUM(IF(MONTH(fecha) = 3,  cantidad, 0)),2) AS mar,
								ROUND(SUM(IF(MONTH(fecha) = 4,  cantidad, 0)),2) AS abr,
								ROUND(SUM(IF(MONTH(fecha) = 5,  cantidad, 0)),2) AS may,
								ROUND(SUM(IF(MONTH(fecha) = 6,  cantidad, 0)),2) AS jun,
								ROUND(SUM(IF(MONTH(fecha) = 7,  cantidad, 0)),2) AS jul,
								ROUND(SUM(IF(MONTH(fecha) = 8,  cantidad, 0)),2) AS ago,
								ROUND(SUM(IF(MONTH(fecha) = 9,  cantidad, 0)),2) AS sep,
								ROUND(SUM(IF(MONTH(fecha) = 10,  cantidad, 0)),2) AS oct,
								ROUND(SUM(IF(MONTH(fecha) = 11,  cantidad, 0)),2) AS nov,
								ROUND(SUM(IF(MONTH(fecha) = 12,  cantidad, 0)),2) AS dic,
								ROUND(SUM(cantidad),2) AS total
                            FROM
                                 (  '.$sql.' UNION ALL SELECT agencia,codigocliente,cliente,desfac,
					            codigo, articulo,
	                            tipodoc,documento,fecha,
	                            cantidad,costotal,
	                            vtatotal,desproducto,
	                            descliente,
	                            IF(codigo="-",net,vtaNeta) AS vtaNeta,
	                            marcod,marca,
					            vencod,vendedor,
					            catcod,cate
                        FROM ( '.$sqlnc.') a'.') b
                                      GROUP BY b.marca
                                      ORDER BY SUM(b.cantidad) DESC';
        }
        else
        {
            $sql='SELECT 	marca,
					            ROUND(SUM(IF(MONTH(fecha) = 1,  vtaneta, 0)),2) AS ene,
								ROUND(SUM(IF(MONTH(fecha) = 2,  vtaneta, 0)),2) AS feb,
								ROUND(SUM(IF(MONTH(fecha) = 3,  vtaneta, 0)),2) AS mar,
								ROUND(SUM(IF(MONTH(fecha) = 4,  vtaneta, 0)),2) AS abr,
								ROUND(SUM(IF(MONTH(fecha) = 5,  vtaneta, 0)),2) AS may,
								ROUND(SUM(IF(MONTH(fecha) = 6,  vtaneta, 0)),2) AS jun,
								ROUND(SUM(IF(MONTH(fecha) = 7,  vtaneta, 0)),2) AS jul,
								ROUND(SUM(IF(MONTH(fecha) = 8,  vtaneta, 0)),2) AS ago,
								ROUND(SUM(IF(MONTH(fecha) = 9,  vtaneta, 0)),2) AS sep,
								ROUND(SUM(IF(MONTH(fecha) = 10,  vtaneta, 0)),2) AS oct,
								ROUND(SUM(IF(MONTH(fecha) = 11,  vtaneta, 0)),2) AS nov,
								ROUND(SUM(IF(MONTH(fecha) = 12,  vtaneta, 0)),2) AS dic,
								ROUND(SUM(vtaneta),2) AS total
                            FROM
                                 (  '.$sql.' UNION ALL SELECT agencia,codigocliente,cliente,desfac,
					            codigo, articulo,
	                            tipodoc,documento,fecha,
	                            cantidad,costotal,
	                            vtatotal,desproducto,
	                            descliente,
	                            IF(codigo="-",net,vtaNeta) AS vtaNeta,
	                            marcod,marca,
					            vencod,vendedor,
					            catcod,cate
                        FROM ( '.$sqlnc.') a'.') b
                                      GROUP BY b.marca
                                      ORDER BY SUM(b.vtaneta) DESC';
        }



        //return $this->getOk($sql);

        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($sql);

        return $this->getOk($list);
        //return $this->getOk($sql);
    }





    public function generaQueryVentas($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente,$virtual1)
    {
        $query=  str_replace('xbase',$bodega,$this->sqlgen);
        if ($bodega=='jcevconsigvirt'){
            $query= str_replace('ybase1','jcevuio1',$query);
            if ($virtual1=='SI'){
                $query= str_replace('zbase1','jcevgyeassem',$query);
            }
            else{
                $query= str_replace('zbase1','jcevuio1',$query);
            }
        }
        else{
            $query=  str_replace('ybase1',$bodega,$query);
            $query=  str_replace('zbase1',$bodega,$query);
        }
        $query=  str_replace('xfinicio',$inicio,$query);
        $query=  str_replace('xffin',$fin,$query);
        $query=  str_replace('xmarc',$marca,$query);
        $query=  str_replace('xprod',$producto,$query);
        //$query=  str_replace('xvend',$vendedor,$query);
        $query=  str_replace('xclie',$cliente,$query);

        return $query;
    }



   public function generaQueryNC($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente)
    {
        $querync=  str_replace('xbase',$bodega,$this->sqlgennc);
        $querync=  str_replace('xfinicio',$inicio,$querync);
        $querync=  str_replace('xffin',$fin,$querync);
        $querync=  str_replace('xmarc',$marca,$querync);
        $querync=  str_replace('xprod',$producto,$querync);
        $querync=  str_replace('xvend',$vendedor,$querync);
        $querync=  str_replace('xclie',$cliente,$querync);

        $querync1=  str_replace('xbase',$bodega,$this->sqlgennc1);
        $querync1=  str_replace('xfinicio',$inicio,$querync1);
        $querync1=  str_replace('xffin',$fin,$querync1);
        $querync1=  str_replace('xmarc',$marca,$querync1);
        $querync1=  str_replace('xprod',$producto,$querync1);
        $querync1=  str_replace('xvend',$vendedor,$querync1);
        $querync1=  str_replace('xclie',$cliente,$querync1);

        $query=$querync.' UNION '.$querync1;

        return $query;
    }

    public function generaQueryNCMatriz($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente)
    {
        $queryncmatriz="SELECT SUBSTRING(numdoc43,1,7) AS agencia,
					             codcte43 AS codigocliente,
						     nomcte01 AS cliente,
						     0 AS desfac,
						     '-' AS  codigo,
						     '-' AS articulo,
						     'NC' AS tipodoc,
						     numdoc43 AS documento,
                             fecdoc43 AS fecha,
						     0 AS cantidad,
						     0 AS costotal,
						     0 AS vtatotal,
						     0 AS desproducto,
						     0 AS descliente,
						     0 AS vtaNeta,
						     (valorabono43/1.12)*-1 AS net,
						      '0' AS marcod,
						     '-' AS marca,
						      numvencob43 AS vencod,
						      (SELECT nomtab FROM jcev.maetab WHERE numtab='73' AND codtab=numvencob43) AS vendedor,
						      catcte01 AS catcod,
						      (SELECT b.desccate AS categoria FROM jcev.categorias a INNER JOIN  jcev.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate
					    FROM xbase.movcte
					    INNER JOIN xbase.maecte ON codcte43=codcte01
					    WHERE tipodoc43 IN ('53')  AND fecdoc43 >= 'xfinicio'  AND fecdoc43 <= 'xffin' AND cvanulado43<>'S' AND tipoNC43<>'P' and ocurren43 in ('00','0000')
                            and case when '0'='xclie' then true else codcte43 in ('xclie') end
                        UNION
                        SELECT SUBSTRING(numdoc43,1,7) AS agencia,
					             codcte43 AS codigocliente,
						     nomcte01 AS cliente,
						     0 AS desfac,
						     '-' AS  codigo,
						     '-' AS articulo,
						     'NC' AS tipodoc,
						     numdoc43 AS documento,
                             fecdoc43 AS fecha,
						     0 AS cantidad,
						     0 AS costotal,
						     0 AS vtatotal,
						     0 AS desproducto,
						     0 AS descliente,
						     0 AS vtaNeta,
						     (valorabono43/1.12)*-1 AS net,
						      '0' AS marcod,
						     '-' AS marca,
						      numvencob43 AS vencod,
						      (SELECT nomtab FROM jcev.maetab WHERE numtab='73' AND codtab=numvencob43) AS vendedor,
						      catcte01 AS catcod,
						      (SELECT b.desccate AS categoria FROM  jcev.categorias a INNER JOIN   jcev.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate
					      FROM xbase.movcte2
					      INNER JOIN xbase.maecte ON codcte43=codcte01
					      WHERE tipodoc43 IN ('53')  AND fecdoc43 >= 'xfinicio'  AND fecdoc43 <= 'xffin' AND cvanulado43<>'S' AND tipoNC43<>'P' and ocurren43 in ('00','0000')
                              and case when '0'='xclie' then true else codcte43 in ('xclie') end";

        $queryncmatriz=  str_replace('xbase',$bodega,$queryncmatriz);
        $queryncmatriz=  str_replace('xfinicio',$inicio,$queryncmatriz);
        $queryncmatriz=  str_replace('xffin',$fin,$queryncmatriz);
        $queryncmatriz=  str_replace('xmarc',$marca,$queryncmatriz);
        $queryncmatriz=  str_replace('xprod',$producto,$queryncmatriz);
        $queryncmatriz=  str_replace('xvend',$vendedor,$queryncmatriz);
        $queryncmatriz=  str_replace('xclie',$cliente,$queryncmatriz);

        $query=$queryncmatriz;

        return $query;
    }


    public function grupoConsulta($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente)
    {


        return $query;
    }

    public function detalleInventarioTransito(Request $request)
    {
        $input = $request->all();
        //return $this->getOk($input);
        $cod=$request['producto_id'];

       try{
            $sql="select c.nombre as nombre_importacion,c.fecha,producto_codigo as producto,cantidad
				    from inventario_transito c,inventario_transitod d
				    where c.id=d.inventario_transito_id and c.liquidado=0
					    and d.producto_codigo=?
			        order by c.fecha desc";
            //return $this->getOk($sql);
            $list = DB::select($sql,[$cod]);
            return $this->getOk($list);

        }catch(\Exception $e) {
            return $this->insertErrCustom($request, $e->getMessage());
        }
    }


    public function categoriaProducto(Request $request)
    {
        $input = $request->all();
       try{
            $sql="SELECT codcate,desccate FROM jcev.categorias cc WHERE tipocate='02' order by desccate";
            $list = DB::connection('mysqlpac')->select($sql);
            return $this->getOk($list);

        }catch(\Exception $e) {
            return $this->insertErrCustom($request, $e->getMessage());
        }
    }




}
