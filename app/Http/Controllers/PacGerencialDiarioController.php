<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use DateTime;
use Carbon\Carbon;

class PacGerencialDiarioController extends Controller
{
    use FormatResponseTrait;

    private $sqlgen="SELECT ALL SUBSTRING(NOCOMP03,1,7) AS agencia,
					        nocte31 AS codigocliente,
					        nomcte31 AS cliente,
					        descto31 AS desfac,
    					    codprod01 AS codigo,
	                        desprod01 AS articulo,
	                        'FA' AS tipodoc,
	                        NOCOMP03 AS documento,
                            fecmov03 AS fecha,
	                        cantid03 AS cantidad,
	                        valor03 AS costotal,
	                        precvta03 AS vtatotal,
	                        descvta03 AS desproducto,
	                        desctotvta03 AS descliente,
	                        (precvta03 - descvta03-desctotvta03) AS vtaNeta,
	                        marca01 AS marcod,
  					        (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca,
	 				        vendcte01 AS vencod,
  					        (SELECT nomtab FROM jcev.maetab WHERE numtab='73' AND codtab =novend31) AS vendedor,
					        catcte01 AS catcod,
  					        (SELECT b.desccate AS categoria FROM jcev.categorias a INNER JOIN  jcev.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate
               FROM xbase.movpro
               INNER JOIN ybase1.maepro ON codprod03 = codprod01
               INNER JOIN zbase1.maefac ON NOCOMP03=nofact31 AND cvanulado31!=9
               INNER JOIN jcev.maecte ON nocte31=codcte01
               WHERE tipotra03 IN ('80') AND cvanulado03 <>'S' AND fecmov03 >= 'xfinicio'  AND fecmov03 <= 'xffin' AND  tipprod01='S' AND statuspro01='S'
                    and case when '0'='xmarc' then true else marca01 in ('xmarc') end
                    and case when '0'='xprod' then true else codprod01 in ('xprod') end
                    and case when '0'='xvend' then true else vendcte01 in ('xvend') end
                    and case when '0'='xclie' then true else nocte31 in ('xclie') end";

    private $sqlgennc="SELECT SUBSTRING(numdoc43,1,7) AS agencia,
					             codcte43 AS codigocliente,
						     nomcte01 AS cliente,
						     0 AS desfac,
						     IFNULL(codprod01,'-') AS codigo,
						     desprod01 AS articulo,
						     'NC' AS tipodoc,
						     numdoc43 AS documento,
                             fecdoc43 AS fecha,
						     IFNULL(cantid03*-1,0) AS cantidad,
						     IFNULL(valor03*-1,0) AS costotal,
						     IFNULL(precvta03*-1,0) AS vtatotal,
						     IFNULL(descvta03*-1,0) AS desproducto,
						     IFNULL(desctotvta03*-1,0) AS descliente,
						     IFNULL((precvta03 - descvta03-desctotvta03)*-1,0) AS vtaNeta,
						     (valorabono43/1.12)*-1 AS net,
						      marca01 AS marcod,
						     (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca,
						      vendcte01 AS vencod,
						      (SELECT nomtab FROM jcev.maetab WHERE numtab='73' AND codtab=numvencob43) AS vendedor,
						      catcte01 AS catcod,
						      (SELECT b.desccate AS categoria FROM jcev.categorias a INNER JOIN  jcev.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate
					      FROM jcev.movcte
					      INNER JOIN jcev.maecte ON codcte43=codcte01
					      INNER JOIN xbase.movpro  ON NOCOMP03=numdoc43 AND tipotra03 IN ('22') AND cvanulado03 <>'S'
					      INNER JOIN xbase.maepro ON codprod03 = codprod01 AND  tipprod01='S'  AND statuspro01='S'
					      WHERE tipodoc43 IN ('53')  AND fecdoc43 >= 'xfinicio'  AND fecdoc43 <= 'xffin' AND cvanulado43<>'S' AND tipoNC43='P'
                                and case when '0'='xmarc' then true else marca01 in ('xmarc') end
                                and case when '0'='xprod' then true else codprod01 in ('xprod') end
                                and case when '0'='xvend' then true else vendcte01 in ('xvend') end
                                and case when '0'='xclie' then true else codcte43 in ('xclie') end";


    private $sqlgennc1="SELECT SUBSTRING(numdoc43,1,7) AS agencia,
					             codcte43 AS codigocliente,
						     nomcte01 AS cliente,
						     0 AS desfac,
						     IFNULL(codprod01,'-') AS codigo,
						     desprod01 AS articulo,
						     'NC' AS tipodoc,
						     numdoc43 AS documento,
                             fecdoc43 AS fecha,
						     IFNULL(cantid03*-1,0) AS cantidad,
						     IFNULL(valor03*-1,0) AS costotal,
						     IFNULL(precvta03*-1,0) AS vtatotal,
						     IFNULL(descvta03*-1,0) AS desproducto,
						     IFNULL(desctotvta03*-1,0) AS descliente,
						     IFNULL((precvta03 - descvta03-desctotvta03)*-1,0) AS vtaNeta,
						     (valorabono43/1.12)*-1 AS net,
						      marca01 AS marcod,
						     (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca,
						      vendcte01 AS vencod,
						      (SELECT nomtab FROM jcev.maetab WHERE numtab='73' AND codtab=numvencob43) AS vendedor,
						      catcte01 AS catcod,
						      (SELECT b.desccate AS categoria FROM jcev.categorias a INNER JOIN  jcev.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate
					      FROM jcev.movcte2
					      INNER JOIN jcev.maecte ON codcte43=codcte01
					      INNER JOIN xbase.movpro  ON NOCOMP03=numdoc43 AND tipotra03 IN ('22') AND cvanulado03 <>'S'
					      INNER JOIN xbase.maepro ON codprod03 = codprod01 AND  tipprod01='S'  AND statuspro01='S'
					      WHERE tipodoc43 IN ('53')  AND fecdoc43 >= 'xfinicio'  AND fecdoc43 <= 'xffin' AND cvanulado43<>'S' AND tipoNC43='P'
                                and case when '0'='xmarc' then true else marca01 in ('xmarc') end
                                and case when '0'='xprod' then true else codprod01 in ('xprod') end
                                and case when '0'='xvend' then true else vendcte01 in ('xvend') end
                                and case when '0'='xclie' then true else codcte43 in ('xclie') end";



    public function __construct()
    {
        //$this->middleware('auth:admin');
    }


    public function ventamescliente(Request $request)
    {
        $input = $request->all();
        $bodega = $request['bodega_id'];
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
                $sqlnc=$sqlnc.' UNION '.$this->generaQueryNCMatriz('jcev',$inicio,$fin,$marca,$producto,$vendedor,$cliente);

        }
        else
        {
            // select de ventas
            $sql=$this->generaQueryVentas($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente);
            // select de notas de credito
            $sqlnc=$this->generaQueryNC($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente);
        }

        if ($unidades=="true")
        {
            //return $this->getOk('trueeeee');
            $sql='SELECT 	codigocliente AS codigo,
				                        cliente AS articulo,
				                        cate AS categoria,
				                        vendedor,
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
                                      GROUP BY b.codigocliente,b.cliente,b.cate,b.vendedor
                                      ORDER BY SUM(b.cantidad) DESC';
        }
        else
        {
            //return $this->getOk('falseeee');
            $sql='SELECT 	codigocliente AS codigo,
				                        cliente AS articulo,
				                        cate AS categoria,
				                        vendedor,
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
                                      GROUP BY b.codigocliente,b.cliente,b.cate,b.vendedor
                                      ORDER BY SUM(b.vtaneta) DESC';
        }



        //return $this->getOk($sql);

        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($sql);

        return $this->getOk($list);
    }



    public function ventamesvendedor(Request $request)
    {
        $input = $request->all();
        $bodega = $request['bodega_id'];
        $bodega_filtro='';

        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['finicio'].' 23:59:00';

        $marca=isset($request['marca_id']) ?$request['marca_id']:'0';
        $producto=isset($request['producto_id']) ?$request['producto_id']:'0';
        $vendedor=isset($request['vendedor_id']) ?$request['vendedor_id']:'0';
        $cliente=isset($request['cliente_id']) ?$request['cliente_id']:'0';
        $unidades=isset($request['unidades']) ?$request['unidades']:false;
        $sql='';
        $sqlnc='';



        if ($bodega=='todas'){
                // select de ventas fecha actual
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
                $sqlnc=$sqlnc.' UNION '.$this->generaQueryNCMatriz('jcev',$inicio,$fin,$marca,$producto,$vendedor,$cliente);

        }
        else
        {
            // select de ventas
            $sql=$this->generaQueryVentas($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente);
            // select de notas de credito
            $sqlnc=$this->generaQueryNC($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente);
        }

        if ($unidades=="true")
        {
            $sql='SELECT 	vendedor,
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
                                      GROUP BY b.vendedor
                                      ORDER BY SUM(b.cantidad) DESC';
        }
        else
        {
            $sql='SELECT 	vendedor,
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
                                      GROUP BY b.vendedor
                                      ORDER BY SUM(b.vtaneta) DESC';
        }



        //return $this->getOk($sql);

        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($sql);

        return $this->getOk($list);
    }


    public function ventamesproducto(Request $request)
    {
        $input = $request->all();
        $bodega = $request['bodega_id'];
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
            $sql=$this->generaQueryVentas($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente);
            // select de notas de credito
            $sqlnc=$this->generaQueryNC($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente);
        }

        if ($unidades=="true")
        {
            $sql='SELECT 	codigo,
                                articulo,
                                marca,
                                (
                                    IFNULL((SELECT cantact01 FROM jcev.maepro WHERE codigo=jcev.maepro.codprod01),0)+
                                    IFNULL((SELECT cantact01 FROM jcevcuenca2.maepro WHERE codigo=jcevcuenca2.maepro.codprod01),0)+
                                    IFNULL((SELECT cantact01 FROM jcevgye1.maepro WHERE codigo=jcevgye1.maepro.codprod01),0)+
                                    IFNULL((SELECT cantact01 FROM jcevuio1.maepro WHERE codigo=jcevuio1.maepro.codprod01),0)+
                                    IFNULL((SELECT cantact01 FROM jcevconsigvirt.maepro WHERE codigo=jcevconsigvirt.maepro.codprod01),0) +
                                    IFNULL((SELECT cantact01 FROM jcevstecvir.maepro WHERE codigo=jcevstecvir.maepro.codprod01),0) +
                                    IFNULL((SELECT cantact01 FROM jcevgyeassem.maepro WHERE codigo=jcevgyeassem.maepro.codprod01),0) +
                                    IFNULL((SELECT cantact01 FROM jcevcuenca1.maepro WHERE codigo=jcevcuenca1.maepro.codprod01),0) +
                                    IFNULL((SELECT cantact01 FROM jcevgye10.maepro WHERE codigo=jcevgye10.maepro.codprod01),0)
                                ) as stock,
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
                                      GROUP BY b.codigo,b.articulo,b.marca
                                      ORDER BY SUM(b.cantidad) DESC';
        }
        else
        {
            $sql='SELECT 	codigo,
                                articulo,
                                marca,
                                (
                                    IFNULL((SELECT cantact01 FROM jcev.maepro WHERE codigo=jcev.maepro.codprod01),0)+
                                    IFNULL((SELECT cantact01 FROM jcevcuenca2.maepro WHERE codigo=jcevcuenca2.maepro.codprod01),0)+
                                    IFNULL((SELECT cantact01 FROM jcevgye1.maepro WHERE codigo=jcevgye1.maepro.codprod01),0)+
                                    IFNULL((SELECT cantact01 FROM jcevuio1.maepro WHERE codigo=jcevuio1.maepro.codprod01),0)+
                                    IFNULL((SELECT cantact01 FROM jcevconsigvirt.maepro WHERE codigo=jcevconsigvirt.maepro.codprod01),0) +
                                    IFNULL((SELECT cantact01 FROM jcevstecvir.maepro WHERE codigo=jcevstecvir.maepro.codprod01),0) +
                                    IFNULL((SELECT cantact01 FROM jcevgyeassem.maepro WHERE codigo=jcevgyeassem.maepro.codprod01),0) +
                                    IFNULL((SELECT cantact01 FROM jcevcuenca1.maepro WHERE codigo=jcevcuenca1.maepro.codprod01),0) +
                                    IFNULL((SELECT cantact01 FROM jcevgye10.maepro WHERE codigo=jcevgye10.maepro.codprod01),0)
                                ) as stock,
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
                                      GROUP BY b.codigo,b.articulo,b.marca
                                      ORDER BY SUM(b.vtaneta) DESC';

        }



        //return $this->getOk($sql);

        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($sql);

        return $this->getOk($list);
    }


    public function ventasmarca(Request $request)
    {
        $input = $request->all();

        $bodega = isset($request['marca_id']) ?$request['bodega_id']:'todas';
        $bodega_filtro='';
        //
        // si es acumulado o solo quieren del del dia actual
        //
        if ($input['acumulado']=='true'){
            $fechai=Carbon::parse($input['finicio']);
            $inicio = Carbon::create($fechai->year,$fechai->month, 1, 0, 0, 0);
            $inicio=$inicio->format('Y-m-d').' 00:00:00';
        }else{
            $inicio=$request['finicio'].' 00:00:00';
        }
        $fin=$request['finicio'].' 23:59:00';
        //
        //revisamos los dias laborables del año anterior en el mismo mes
        //
        $fanterior = $this->DiasLaborables($inicio);

        //return $this->getOk($fanterior);

         if ($input['acumulado']=='true'){
            return $this->getOk($fanterior);
            $inicio1 = Carbon::create($fanterior->year,$fanterior->month, 1, 0, 0, 0);
            return $this->getOk($inicio1);
            $inicio1=$inicio1->format('Y-m-d').' 00:00:00';
        }else{
            $fanterior=$fanterior->format('Y-m-d');
            $inicio1=$fanterior.' 00:00:00';
        }
        //return $this->getOk($inicio1);
        $fin1=$fanterior.' 23:59:00';

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
                // select de ventas año anterior
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcev',$inicio1,$fin1,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevcuenca2',$inicio1,$fin1,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevcuenca1',$inicio1,$fin1,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevgye1',$inicio1,$fin1,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevgye10',$inicio1,$fin1,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevuio1',$inicio1,$fin1,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevconsigvirt',$inicio1,$fin1,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevgyeassem',$inicio1,$fin1,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevconsigvirt',$inicio1,$fin1,$marca,$producto,$vendedor,$cliente,'SI');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('jcevstecvir',$inicio1,$fin1,$marca,$producto,$vendedor,$cliente,'NO');

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
                // select de notas de credito año anterior
                $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcev',$inicio1,$fin1,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevconsigvirt',$inicio1,$fin1,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevcuenca2',$inicio1,$fin1,$marca,$producto,$vendedor,$cliente);
                //$sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevcuenca1',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevgye1',$inicio1,$fin1,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevgye10',$inicio1,$fin1,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevuio1',$inicio1,$fin1,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevgyeassem',$inicio1,$fin1,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION '.$this->generaQueryNC('jcevstecvir',$inicio1,$fin1,$marca,$producto,$vendedor,$cliente);
        }
        else
        {
            // select de ventas
            $sql=$this->generaQueryVentas($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente);
            // select de notas de credito
            $sqlnc=$this->generaQueryNC($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente);
        }


            $sql="SELECT marca,
                        ROUND(SUM(IF(date(fecha) = '".$inicio."',  vtaneta, 0)),2) AS usd_actual,
                        ROUND(SUM(IF(date(fecha) = '".$inicio."',  cantidad, 0)),2) AS cantidad_actual,
                        ROUND(SUM(IF(date(fecha) = '".$inicio1."',  vtaneta, 0)),2) AS usd_anterior,
                        ROUND(SUM(IF(date(fecha) = '".$inicio1."',  cantidad, 0)),2) AS cantidad_anterior,
                        ROUND(ifnull(SUM(IF(date(fecha) = '".$inicio."',  vtaneta, 0))/SUM(IF(date(fecha) = '".$inicio1."',  vtaneta, 0)),0)*100,2) as incremento

                            FROM
                                 (  ".$sql." UNION ALL SELECT agencia,codigocliente,cliente,desfac,
					            codigo, articulo,
	                            tipodoc,documento,fecha,
	                            cantidad,costotal,
	                            vtatotal,desproducto,
	                            descliente,
	                            IF(codigo='-',net,vtaNeta) AS vtaNeta,
	                            marcod,marca,
					            vencod,vendedor,
					            catcod,cate
                        FROM ( ".$sqlnc.") a) b
                                      GROUP BY b.marca
                                      ORDER BY SUM(b.cantidad) DESC";

            //echo ($sql);


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
        $query=  str_replace('xvend',$vendedor,$query);
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
						      vendcte01 AS vencod,
						      (SELECT nomtab FROM jcev.maetab WHERE numtab='73' AND codtab=numvencob43) AS vendedor,
						      catcte01 AS catcod,
						      (SELECT b.desccate AS categoria FROM jcev.categorias a INNER JOIN  jcev.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate
					    FROM xbase.movcte
					    INNER JOIN xbase.maecte ON codcte43=codcte01
					    WHERE tipodoc43 IN ('53')  AND fecdoc43 >= 'xfinicio'  AND fecdoc43 <= 'xffin' AND cvanulado43<>'S' AND tipoNC43<>'P'
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
						      vendcte01 AS vencod,
						      (SELECT nomtab FROM jcev.maetab WHERE numtab='73' AND codtab=numvencob43) AS vendedor,
						      catcte01 AS catcod,
						      (SELECT b.desccate AS categoria FROM  jcev.categorias a INNER JOIN   jcev.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate
					      FROM xbase.movcte2
					      INNER JOIN xbase.maecte ON codcte43=codcte01
					      WHERE tipodoc43 IN ('53')  AND fecdoc43 >= 'xfinicio'  AND fecdoc43 <= 'xffin' AND cvanulado43<>'S' AND tipoNC43<>'P'
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


    public function exportLabel($id)
    {
        $sql='SELECT codprod01 as codigo,desprod01 as producto from jcev.maepro where codprod01 in("BICI308A24V","BICI89","BUZ01")';
        $order = DB::connection('mysqlpac')->select($sql);
        $customPaper = array(0,0,567.00,283.80);
        $pdf = PDF::loadView('label',compact('order')
        )->setPaper([0, 0, 141.73,283.47 ], 'landscape');

        return $pdf->stream('label1.pdf');
    }


    //public function DiasLaborables($fecha){
    public function DiasLaborables($inicio){

        $fecha=Carbon::parse($inicio);
        $fechainicio = Carbon::create($fecha->year,$fecha->month, 1, 0, 0, 0);

        $dias_laborable=0;

        while ($fecha >= $fechainicio){
            $diasemana=$fechainicio->format("l");
            if (strtoupper($diasemana)!='SATURDAY' and strtoupper($diasemana)!='SUNDAY'){
                $dias_laborable++;
            }
            $fechainicio->addDays(1);
        }

        $fechainicioanterior = Carbon::create(($fecha->year)-1,$fecha->month, 1, 0, 0, 0);

        $dias=0;
        $dias_laborable_anterior=0;
        while ($dias < $dias_laborable){
            $diasemana=$fechainicioanterior->format("l");
            if (strtoupper($diasemana)!='SATURDAY' and strtoupper($diasemana)!='SUNDAY'){
                $dias++;
            }

            $dias_laborable_anterior++;
            $fechainicioanterior->addDays(1);
        }
        //return $fechainicioanterior;

        $fechaanterior = Carbon::create(($fecha->year)-1,$fecha->month, $dias_laborable_anterior, 0, 0, 0);
        return $fechaanterior;
    }


}
