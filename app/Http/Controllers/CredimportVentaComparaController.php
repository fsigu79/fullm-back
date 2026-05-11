<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use PDF;
use Carbon\Carbon;

class CredimportVentaComparaController extends Controller
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
  					        (SELECT DISTINCT nomtab FROM vintipart.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca,
	 				        vendcte01 AS vencod,
  					        (SELECT nomtab FROM vintipart.maetab WHERE numtab='73' AND codtab =novend31) AS vendedor,
					        catcte01 AS catcod,
  					        (SELECT a.desccate AS categoria FROM vintipart.categorias a INNER JOIN  vintipart.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate,
                            (SELECT DISTINCT nomtab FROM vintipart.maetab WHERE numtab = '34' AND codtab <> '' AND codtab = vintipart.maecte.canton) AS ciudad
               FROM xbase.movpro
               INNER JOIN ybase1.maepro ON codprod03 = codprod01
               INNER JOIN zbase1.maefac ON NOCOMP03=nofact31 AND cvanulado31!=9
               INNER JOIN vintipart.maecte ON nocte31=codcte01
               WHERE tipotra03 IN ('80') AND cvanulado03 <>'S' AND ((fecmov03 >= 'xfinicio'  AND fecmov03 <= 'xffin') or (fecmov03 >= 'yfinicioa'  AND fecmov03 <= 'yffina')) AND  tipprod01='S'
                    and case when '0'='xmarc' then true else marca01 in ('xmarc') end
                    and case when '0'='xprod' then true else codprod01 in ('xprod') end
                    and case when '0'='xvend' then true else vendcte01 in ('xvend') end
                    and case when '0'='xclie' then true else nocte31 in ('xclie') end";


private $sqlgencom="SELECT ALL SUBSTRING(NOCOMP03,1,7) AS agencia,
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
  					        (SELECT DISTINCT nomtab FROM vintipart.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca,
	 				        vendcte01 AS vencod,
  					        (SELECT nomtab FROM vintipart.maetab WHERE numtab='73' AND codtab =novend31) AS vendedor,
					        catcte01 AS catcod,
  					        (SELECT a.desccate AS categoria FROM vintipart.categorias a INNER JOIN  vintipart.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate,
                            (SELECT DISTINCT nomtab FROM vintipart.maetab WHERE numtab = '34' AND codtab <> '' AND codtab = vintipart.maecte.canton) AS ciudad
               FROM xbase.movpro
               INNER JOIN ybase1.maepro ON codprod03 = codprod01
               INNER JOIN zbase1.maefac ON NOCOMP03=nofact31 AND cvanulado31!=9
               INNER JOIN vintipart.maecte ON nocte31=codcte01
               WHERE tipotra03 IN ('80') AND cvanulado03 <>'S' AND ((fecmov03 >= 'xfinicio'  AND fecmov03 <= 'xffin') or (fecmov03 >= 'yfinicioa'  AND fecmov03 <= 'yffina')
                    or (fecmov03 >= 'zfinicioa'  AND fecmov03 <= 'zffina')) AND  tipprod01='S'
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
						     (SELECT DISTINCT nomtab FROM vintipart.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca,
						      vendcte01 AS vencod,
						      (SELECT nomtab FROM vintipart.maetab WHERE numtab='73' AND codtab=numvencob43) AS vendedor,
						      catcte01 AS catcod,
						      (SELECT a.desccate AS categoria FROM vintipart.categorias a INNER JOIN  vintipart.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate,
                              (SELECT DISTINCT nomtab FROM vintipart.maetab WHERE numtab = '34' AND codtab <> '' AND codtab = vintipart.maecte.canton) AS ciudad
					      FROM vintipart.movcte
					      INNER JOIN vintipart.maecte ON codcte43=codcte01
					      INNER JOIN xbase.movpro  ON NOCOMP03=numdoc43 AND tipotra03 IN ('22') AND cvanulado03 <>'S'
					      INNER JOIN xbase.maepro ON codprod03 = codprod01 AND  tipprod01='S'  AND statuspro01='S'
					      WHERE tipodoc43 IN ('53')  AND ((fecdoc43 >= 'xfinicio'  AND fecdoc43<= 'xffin') or (fecdoc43 >= 'yfinicioa'  AND fecdoc43 <= 'yffina'))  AND cvanulado43<>'S' AND tipoNC43='P' and ocurren43 in ('00','0000')
                                and case when '0'='xmarc' then true else marca01 in ('xmarc') end
                                and case when '0'='xprod' then true else codprod01 in ('xprod') end
                                and case when '0'='xvend' then true else vendcte01 in ('xvend') end
                                and case when '0'='xclie' then true else codcte43 in ('xclie') end";

    private $sqlgenncCom="SELECT SUBSTRING(numdoc43,1,7) AS agencia,
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
						     (SELECT DISTINCT nomtab FROM vintipart.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca,
						      vendcte01 AS vencod,
						      (SELECT nomtab FROM vintipart.maetab WHERE numtab='73' AND codtab=numvencob43) AS vendedor,
						      catcte01 AS catcod,
						      (SELECT a.desccate AS categoria FROM vintipart.categorias a INNER JOIN  vintipart.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate,
                              (SELECT DISTINCT nomtab FROM vintipart.maetab WHERE numtab = '34' AND codtab <> '' AND codtab = vintipart.maecte.canton) AS ciudad
					      FROM vintipart.movcte
					      INNER JOIN vintipart.maecte ON codcte43=codcte01
					      INNER JOIN xbase.movpro  ON NOCOMP03=numdoc43 AND tipotra03 IN ('22') AND cvanulado03 <>'S'
					      INNER JOIN xbase.maepro ON codprod03 = codprod01 AND  tipprod01='S'  AND statuspro01='S'
					      WHERE tipodoc43 IN ('53')  AND ((fecdoc43 >= 'xfinicio'  AND fecdoc43<= 'xffin') or (fecdoc43 >= 'yfinicioa'  AND fecdoc43 <= 'yffina')
                                or (fecdoc43 >= 'zfinicioa'  AND fecdoc43 <= 'zffina'))  AND cvanulado43<>'S' AND tipoNC43='P' and ocurren43 in ('00','0000')
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
						     (SELECT DISTINCT nomtab FROM vintipart.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca,
						      vendcte01 AS vencod,
						      (SELECT nomtab FROM vintipart.maetab WHERE numtab='73' AND codtab=numvencob43) AS vendedor,
						      catcte01 AS catcod,
						      (SELECT a.desccate AS categoria FROM vintipart.categorias a INNER JOIN  vintipart.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate,
                              (SELECT DISTINCT nomtab FROM vintipart.maetab WHERE numtab = '34' AND codtab <> '' AND codtab = vintipart.maecte.canton) AS ciudad
					      FROM vintipart.movcte2
					      INNER JOIN vintipart.maecte ON codcte43=codcte01
					      INNER JOIN xbase.movpro  ON NOCOMP03=numdoc43 AND tipotra03 IN ('22') AND cvanulado03 <>'S'
					      INNER JOIN xbase.maepro ON codprod03 = codprod01 AND  tipprod01='S'  AND statuspro01='S'
					      WHERE tipodoc43 IN ('53')  AND ((fecdoc43 >= 'xfinicio'  AND fecdoc43 <= 'xffin') or (fecdoc43 >= 'yfinicioa'  AND fecdoc43 <= 'yffina')) AND cvanulado43<>'S' AND tipoNC43='P' and ocurren43 in ('00','0000')
                                and case when '0'='xmarc' then true else marca01 in ('xmarc') end
                                and case when '0'='xprod' then true else codprod01 in ('xprod') end
                                and case when '0'='xvend' then true else vendcte01 in ('xvend') end
                                and case when '0'='xclie' then true else codcte43 in ('xclie') end";


private $sqlgennc1Com="SELECT SUBSTRING(numdoc43,1,7) AS agencia,
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
						     (SELECT DISTINCT nomtab FROM vintipart.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca,
						      vendcte01 AS vencod,
						      (SELECT nomtab FROM vintipart.maetab WHERE numtab='73' AND codtab=numvencob43) AS vendedor,
						      catcte01 AS catcod,
						      (SELECT a.desccate AS categoria FROM vintipart.categorias a INNER JOIN  vintipart.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate,
                              (SELECT DISTINCT nomtab FROM vintipart.maetab WHERE numtab = '34' AND codtab <> '' AND codtab = vintipart.maecte.canton) AS ciudad
					      FROM vintipart.movcte2
					      INNER JOIN vintipart.maecte ON codcte43=codcte01
					      INNER JOIN xbase.movpro  ON NOCOMP03=numdoc43 AND tipotra03 IN ('22') AND cvanulado03 <>'S'
					      INNER JOIN xbase.maepro ON codprod03 = codprod01 AND  tipprod01='S'  AND statuspro01='S'
					      WHERE tipodoc43 IN ('53')  AND ((fecdoc43 >= 'xfinicio'  AND fecdoc43 <= 'xffin') or (fecdoc43 >= 'yfinicioa'  AND fecdoc43 <= 'yffina')
                                 or (fecdoc43 >= 'zfinicioa'  AND fecdoc43 <= 'zffina')) AND cvanulado43<>'S' AND tipoNC43='P' and ocurren43 in ('00','0000')
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

        $fecha=Carbon::parse($inicio);
        $anoactual=($fecha->year);
        $anoanterior=($fecha->year)-1;
        $fecha1=Carbon::parse($fin);
        $inicioa=Carbon::create(($fecha->year)-1,$fecha->month, $fecha->day, 0, 0, 0);
        $inicioa=$inicioa->format('Y-m-d').' 00:00:00';
        $fina=Carbon::create(($fecha1->year)-1,$fecha1->month, $fecha1->day, 23,59, 0);
        $fina=$fina->format('Y-m-d').' 23:59:00';

        $marca=isset($request['marca_id']) ?$request['marca_id']:'0';
        $producto=isset($request['producto_id']) ?$request['producto_id']:'0';
        $vendedor=isset($request['vendedor_id']) ?$request['vendedor_id']:'0';
        $cliente=isset($request['cliente_id']) ?$request['cliente_id']:'0';
        $unidades=isset($request['unidades']) ?$request['unidades']:false;
        $sql='';
        $sqlnc='';




        if ($bodega=='todas'){
                // select de ventas
                 $sql=$this->generaQueryVentas('vintipart',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('vintipartcuen1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('vintipartuio',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('vintipartgye3',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);
                // select de notas de credito
                $sqlnc=$this->generaQueryNC('vintipart',$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('vintipartcuen1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('vintipartuio',$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('vintipartgye3',$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);

        }
        else
        {
            // select de ventas
            $sql=$this->generaQueryVentas($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);
            // select de notas de credito
            $sqlnc=$this->generaQueryNC($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);
        }

        if ($unidades=="true")
        {
            //return $this->getOk('trueeeee');
            $sql='SELECT 	codigocliente AS codigo,
				                        cliente AS articulo,
				                        cate AS categoria,ciudad,
					                    ROUND(SUM(IF(YEAR(fecha) = '.$anoanterior.', cantidad, 0)),2) AS anterior,
					                    ROUND(SUM(IF(YEAR(fecha) = '.$anoactual.', cantidad, 0)),2) AS actual,
					                    ROUND(ifnull(SUM(IF(YEAR(fecha) = '.$anoactual.', cantidad, 0)) /SUM(IF(YEAR(fecha) = '.$anoanterior.', cantidad, 0))-1,0)*100,2) as incremento
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
					            catcod,cate,ciudad
                        FROM ( '.$sqlnc.') a'.') b
                                      GROUP BY b.codigocliente,b.cliente,b.cate,b.ciudad
                                      ORDER BY SUM(b.cantidad) DESC';
        }
        else
        {
            //return $this->getOk('falseeee');
            //DB::statement(DB::raw('SET @i = 0;'));

            $sql='SELECT 1 as numreg,codigocliente AS codigo,
				                        cliente AS articulo,
				                        cate AS categoria,ciudad,
					                    ROUND(SUM(IF(YEAR(fecha) = '.$anoanterior.', vtaneta, 0)),2) AS anterior,
					                    ROUND(SUM(IF(YEAR(fecha) = '.$anoactual.', vtaneta, 0)),2) AS actual,
                                        ROUND(ifnull(SUM(IF(YEAR(fecha) = '.$anoactual.', vtaneta, 0)) /SUM(IF(YEAR(fecha) = '.$anoanterior.', vtaneta, 0))-1,0)*100,2) as incremento
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
					            catcod,cate,ciudad
                        FROM ( '.$sqlnc.') a'.') b
                                      GROUP BY b.codigocliente,b.cliente,b.cate,b.ciudad
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
        $fin=$request['ffin'].' 23:59:00';
        $fecha=Carbon::parse($inicio);
        $anoactual=($fecha->year);
        $anoanterior=($fecha->year)-1;
        $fecha1=Carbon::parse($fin);
        $inicioa=Carbon::create(($fecha->year)-1,$fecha->month, $fecha->day, 0, 0, 0);
        $inicioa=$inicioa->format('Y-m-d').' 00:00:00';
        $fina=Carbon::create(($fecha1->year)-1,$fecha1->month, $fecha1->day, 23,59, 0);
        $fina=$fina->format('Y-m-d').' 23:59:00';

        $marca=isset($request['marca_id']) ?$request['marca_id']:'0';
        $producto=isset($request['producto_id']) ?$request['producto_id']:'0';
        $vendedor=isset($request['vendedor_id']) ?$request['vendedor_id']:'0';
        $cliente=isset($request['cliente_id']) ?$request['cliente_id']:'0';
        $unidades=isset($request['unidades']) ?$request['unidades']:false;
        $sql='';
        $sqlnc='';



        if ($bodega=='todas'){
                // select de ventas
                $sql=$this->generaQueryVentas('vintipart',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('vintipartcuen1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('vintipartuio',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('vintipartgye3',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);
                // select de notas de credito
                $sqlnc=$this->generaQueryNC('vintipart',$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('vintipartcuen1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('vintipartuio',$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('vintipartgye3',$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);
        }
        else
        {
            // select de ventas
            $sql=$this->generaQueryVentas($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);
            // select de notas de credito
            $sqlnc=$this->generaQueryNC($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);
        }
        if ($unidades=="true")
        {
            $sql='SELECT 	vendedor,
					            ROUND(SUM(IF(YEAR(fecha) = '.$anoanterior.', cantidad, 0)),2) AS anterior,
					            ROUND(SUM(IF(YEAR(fecha) = '.$anoactual.', cantidad, 0)),2) AS actual,
					            ROUND(ifnull(SUM(IF(YEAR(fecha) = '.$anoactual.', cantidad, 0)) /SUM(IF(YEAR(fecha) = '.$anoanterior.', cantidad, 0))-1,0)*100,2) as incremento
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
					            catcod,cate,ciudad
                        FROM ( '.$sqlnc.') a'.') b
                                      GROUP BY b.vendedor
                                      ORDER BY SUM(b.cantidad) DESC';
        }
        else
        {
            $sql='SELECT 	vendedor,
					            ROUND(SUM(IF(YEAR(fecha) = '.$anoanterior.', vtaneta, 0)),2) AS anterior,
					            ROUND(SUM(IF(YEAR(fecha) = '.$anoactual.', vtaneta, 0)),2) AS actual,
					            ROUND(ifnull(SUM(IF(YEAR(fecha) = '.$anoactual.', vtaneta, 0)) /SUM(IF(YEAR(fecha) = '.$anoanterior.', vtaneta, 0))-1,0)*100,2) as incremento
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
					            catcod,cate,ciudad
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
        $fecha=Carbon::parse($inicio);
        $anoactual=($fecha->year);
        $anoanterior=($fecha->year)-1;
        $fecha1=Carbon::parse($fin);
        $inicioa=Carbon::create(($fecha->year)-1,$fecha->month, $fecha->day, 0, 0, 0);
        $inicioa=$inicioa->format('Y-m-d').' 00:00:00';
        $fina=Carbon::create(($fecha1->year)-1,$fecha1->month, $fecha1->day, 23,59, 0);
        $fina=$fina->format('Y-m-d').' 23:59:00';
        $marca=isset($request['marca_id']) ?$request['marca_id']:'0';
        $producto=isset($request['producto_id']) ?$request['producto_id']:'0';
        $vendedor=isset($request['vendedor_id']) ?$request['vendedor_id']:'0';
        $cliente=isset($request['cliente_id']) ?$request['cliente_id']:'0';
        $unidades=isset($request['unidades']) ?$request['unidades']:false;
        $sql='';
        $sqlnc='';



        if ($bodega=='todas'){
                // select de ventas
                $sql=$this->generaQueryVentas('vintipart',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('vintipartcuen1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('vintipartuio',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('vintipartgye3',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);

                // select de notas de credito
                $sqlnc=$this->generaQueryNC('vintipart',$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('vintipartcuen1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('vintipartuio',$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('vintipartgye3',$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);

        }
        else
        {
            // select de ventas
            $sql=$this->generaQueryVentas($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);
            // select de notas de credito
            $sqlnc=$this->generaQueryNC($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);
        }

        if ($unidades=="true")
        {
            $sql='SELECT 	codigo,
                                articulo,
                                marca,
                                ROUND(SUM(IF(YEAR(fecha) = '.$anoanterior.', cantidad, 0)),2) AS anterior,
					            ROUND(SUM(IF(YEAR(fecha) = '.$anoactual.', cantidad, 0)),2) AS actual,
					            ROUND(
                                    ifnull(SUM(IF(YEAR(fecha) = '.$anoactual.', cantidad, 0)) /SUM(IF(YEAR(fecha) = '.$anoanterior.', cantidad, 0))-1,0)*100,2) as incremento
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
					            catcod,cate,ciudad
                        FROM ( '.$sqlnc.') a'.') b
                                      GROUP BY b.codigo,b.articulo,b.marca
                                      ORDER BY SUM(b.cantidad) DESC';
        }
        else
        {
            $sql='SELECT 	codigo,
                                articulo,
                                marca,
                                ROUND(SUM(IF(YEAR(fecha) = '.$anoanterior.', vtaneta, 0)),2) AS anterior,
					            ROUND(SUM(IF(YEAR(fecha) = '.$anoactual.', vtaneta, 0)),2) AS actual,
								ROUND(ifnull(SUM(IF(YEAR(fecha) = '.$anoactual.', vtaneta, 0)) /SUM(IF(YEAR(fecha) = '.$anoanterior.', vtaneta, 0))-1,0)*100,2) as incremento
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
					            catcod,cate,ciudad
                        FROM ( '.$sqlnc.') a'.') b
                                      GROUP BY b.codigo,b.articulo,b.marca
                                      ORDER BY SUM(b.vtaneta) DESC';

        }



        //return $this->getOk($sql);

        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($sql);

        return $this->getOk($list);
    }



    public function ventamesmarca(Request $request)
    {
        $input = $request->all();
        $bodega = isset($request['marca_id']) ?$request['bodega_id']:'todas';
        $bodega_filtro='';
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $fecha=Carbon::parse($inicio);
        $anoactual=($fecha->year);
        $anoanterior=($fecha->year)-1;
        $fecha1=Carbon::parse($fin);
        $inicioa=Carbon::create(($fecha->year)-1,$fecha->month, $fecha->day, 0, 0, 0);
        $inicioa=$inicioa->format('Y-m-d').' 00:00:00';
        $fina=Carbon::create(($fecha1->year)-1,$fecha1->month, $fecha1->day, 23,59, 0);
        $fina=$fina->format('Y-m-d').' 23:59:00';
        $marca=isset($request['marca_id']) ?$request['marca_id']:'0';
        $producto=isset($request['producto_id']) ?$request['producto_id']:'0';
        $vendedor=isset($request['vendedor_id']) ?$request['vendedor_id']:'0';
        $cliente=isset($request['cliente_id']) ?$request['cliente_id']:'0';
        $unidades=isset($request['unidades']) ?$request['unidades']:false;
        $sql='';
        $sqlnc='';



        if ($bodega=='todas'){
                // select de ventas
                $sql=$this->generaQueryVentas('vintipart',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('vintipartcuen1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('vintipartuio',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('vintipartgye3',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);
                // select de notas de credito
                $sqlnc=$this->generaQueryNC('vintipart',$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('vintipartcuen1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('vintipartuio',$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('vintipartgye3',$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);
        }
        else
        {
            // select de ventas
            $sql=$this->generaQueryVentas($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO',$inicioa,$fina);
            // select de notas de credito
            $sqlnc=$this->generaQueryNC($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina);
        }

        if ($unidades=="true")
        {
            $sql='SELECT 	marca,
					            ROUND(SUM(IF(YEAR(fecha) = '.$anoanterior.', cantidad, 0)),2) AS anterior,
					            ROUND(SUM(IF(YEAR(fecha) = '.$anoactual.', cantidad, 0)),2) AS actual,
								ROUND(ifnull(SUM(IF(YEAR(fecha) = '.$anoactual.', cantidad, 0)) /SUM(IF(YEAR(fecha) = '.$anoanterior.', cantidad, 0))-1,0)*100,2) as incremento
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
					            catcod,cate,ciudad
                        FROM ( '.$sqlnc.') a'.') b
                                      GROUP BY b.marca
                                      ORDER BY SUM(b.cantidad) DESC';
        }
        else
        {
            $sql='SELECT 	marca,
					            ROUND(SUM(IF(YEAR(fecha) = '.$anoanterior.', vtaneta, 0)),2) AS anterior,
					            ROUND(SUM(IF(YEAR(fecha) = '.$anoactual.', vtaneta, 0)),2) AS actual,
								ROUND(ifnull(SUM(IF(YEAR(fecha) = '.$anoactual.', vtaneta, 0)) /SUM(IF(YEAR(fecha) = '.$anoanterior.', vtaneta, 0))-1,0)*100,2) as incremento
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
					            catcod,cate,ciudad
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




    public function generaQueryVentas($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente,$virtual1,$inicioa,$fina)
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

        $query=  str_replace('yfinicioa',$inicioa,$query);
        $query=  str_replace('yffina',$fina,$query);


        return $query;
    }

    public function generaQueryVentasCom($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente,$virtual1,$inicioa,$fina,$inicioa1,$fina1)
    {
        $query=  str_replace('xbase',$bodega,$this->sqlgencom);
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

        $query=  str_replace('yfinicioa',$inicioa,$query);
        $query=  str_replace('yffina',$fina,$query);

        $query=  str_replace('zfinicioa',$inicioa1,$query);
        $query=  str_replace('zffina',$fina1,$query);


        return $query;
    }



   public function generaQueryNC($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina)
    {
        $querync=  str_replace('xbase',$bodega,$this->sqlgennc);
        $querync=  str_replace('xfinicio',$inicio,$querync);
        $querync=  str_replace('xffin',$fin,$querync);
        $querync=  str_replace('xmarc',$marca,$querync);
        $querync=  str_replace('xprod',$producto,$querync);
        $querync=  str_replace('xvend',$vendedor,$querync);
        $querync=  str_replace('xclie',$cliente,$querync);

        $querync=  str_replace('yfinicioa',$inicioa,$querync);
        $querync=  str_replace('yffina',$fina,$querync);


        $querync1=  str_replace('xbase',$bodega,$this->sqlgennc1);
        $querync1=  str_replace('xfinicio',$inicio,$querync1);
        $querync1=  str_replace('xffin',$fin,$querync1);
        $querync1=  str_replace('xmarc',$marca,$querync1);
        $querync1=  str_replace('xprod',$producto,$querync1);
        $querync1=  str_replace('xvend',$vendedor,$querync1);
        $querync1=  str_replace('xclie',$cliente,$querync1);

        $querync1=  str_replace('yfinicioa',$inicioa,$querync1);
        $querync1=  str_replace('yffina',$fina,$querync1);

        $query=$querync.' UNION ALL '.$querync1;



        return $query;
    }

    public function generaQueryNCCom($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina,$inicioa1,$fina1)
    {
        $querync=  str_replace('xbase',$bodega,$this->sqlgenncCom);
        $querync=  str_replace('xfinicio',$inicio,$querync);
        $querync=  str_replace('xffin',$fin,$querync);
        $querync=  str_replace('xmarc',$marca,$querync);
        $querync=  str_replace('xprod',$producto,$querync);
        $querync=  str_replace('xvend',$vendedor,$querync);
        $querync=  str_replace('xclie',$cliente,$querync);

        $querync=  str_replace('yfinicioa',$inicioa,$querync);
        $querync=  str_replace('yffina',$fina,$querync);

        $querync=  str_replace('zfinicioa',$inicioa1,$querync);
        $querync=  str_replace('zffina',$fina1,$querync);


        $querync1=  str_replace('xbase',$bodega,$this->sqlgennc1Com);
        $querync1=  str_replace('xfinicio',$inicio,$querync1);
        $querync1=  str_replace('xffin',$fin,$querync1);
        $querync1=  str_replace('xmarc',$marca,$querync1);
        $querync1=  str_replace('xprod',$producto,$querync1);
        $querync1=  str_replace('xvend',$vendedor,$querync1);
        $querync1=  str_replace('xclie',$cliente,$querync1);

        $querync1=  str_replace('yfinicioa',$inicioa,$querync1);
        $querync1=  str_replace('yffina',$fina,$querync1);

        $querync1=  str_replace('zfinicioa',$inicioa1,$querync1);
        $querync1=  str_replace('zffina',$fina1,$querync1);

        $query=$querync.' UNION ALL '.$querync1;



        return $query;
    }

    public function generaQueryNCMatriz($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina)
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
						      (SELECT nomtab FROM vintipart.maetab WHERE numtab='73' AND codtab=numvencob43) AS vendedor,
						      catcte01 AS catcod,
						      (SELECT a.desccate AS categoria FROM vintipart.categorias a INNER JOIN  vintipart.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate,
                              (SELECT DISTINCT nomtab FROM vintipart.maetab WHERE numtab = '34' AND codtab <> '' AND codtab = vintipart.maecte.canton) AS ciudad
					    FROM xbase.movcte
					    INNER JOIN xbase.maecte ON codcte43=codcte01
					    WHERE tipodoc43 IN ('53')  AND ((fecdoc43 >= 'xfinicio'  AND fecdoc43 <= 'xffin') or (fecdoc43 >= 'yfinicioa'  AND fecdoc43 <= 'yffina'))  AND cvanulado43<>'S' AND tipoNC43<>'P' and ocurren43 in ('00','0000')
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
						      (SELECT nomtab FROM vintipart.maetab WHERE numtab='73' AND codtab=numvencob43) AS vendedor,
						      catcte01 AS catcod,
						      (SELECT a.desccate AS categoria FROM  vintipart.categorias a INNER JOIN   vintipart.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate,
                              (SELECT DISTINCT nomtab FROM vintipart.maetab WHERE numtab = '34' AND codtab <> '' AND codtab = vintipart.maecte.canton) AS ciudad
					      FROM xbase.movcte2
					      INNER JOIN xbase.maecte ON codcte43=codcte01
					      WHERE tipodoc43 IN ('53')  AND ((fecdoc43 >= 'xfinicio'  AND fecdoc43 <= 'xffin') or (fecdoc43 >= 'yfinicioa'  AND fecdoc43 <= 'yffina')) AND cvanulado43<>'S' AND tipoNC43<>'P' and ocurren43 in ('00','0000')
                              and case when '0'='xclie' then true else codcte43 in ('xclie') end";

        $queryncmatriz=  str_replace('xbase',$bodega,$queryncmatriz);
        $queryncmatriz=  str_replace('xfinicio',$inicio,$queryncmatriz);
        $queryncmatriz=  str_replace('xffin',$fin,$queryncmatriz);
        $queryncmatriz=  str_replace('xmarc',$marca,$queryncmatriz);
        $queryncmatriz=  str_replace('xprod',$producto,$queryncmatriz);
        $queryncmatriz=  str_replace('xvend',$vendedor,$queryncmatriz);
        $queryncmatriz=  str_replace('xclie',$cliente,$queryncmatriz);

        $queryncmatriz=  str_replace('yfinicioa',$inicioa,$queryncmatriz);
        $queryncmatriz=  str_replace('yffina',$fina,$queryncmatriz);

        $query=$queryncmatriz;


        return $query;
    }


     public function generaQueryNCMatrizCom($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente,$inicioa,$fina,$inicioa1,$fina1)
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
						      (SELECT nomtab FROM vintipart.maetab WHERE numtab='73' AND codtab=numvencob43) AS vendedor,
						      catcte01 AS catcod,
						      (SELECT a.desccate AS categoria FROM vintipart.categorias a INNER JOIN  vintipart.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate,
                              (SELECT DISTINCT nomtab FROM vintipart.maetab WHERE numtab = '34' AND codtab <> '' AND codtab = vintipart.maecte.canton) AS ciudad
					    FROM xbase.movcte
					    INNER JOIN xbase.maecte ON codcte43=codcte01
					    WHERE tipodoc43 IN ('53')  AND ((fecdoc43 >= 'xfinicio'  AND fecdoc43 <= 'xffin') or (fecdoc43 >= 'yfinicioa'  AND fecdoc43 <= 'yffina')
                                or (fecdoc43 >= 'zfinicioa'  AND fecdoc43 <= 'zffina'))  AND cvanulado43<>'S' AND tipoNC43<>'P' and ocurren43 in ('00','0000')
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
						      (SELECT nomtab FROM vintipart.maetab WHERE numtab='73' AND codtab=numvencob43) AS vendedor,
						      catcte01 AS catcod,
						      (SELECT a.desccate AS categoria FROM  vintipart.categorias a INNER JOIN   vintipart.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate,
                              (SELECT DISTINCT nomtab FROM vintipart.maetab WHERE numtab = '34' AND codtab <> '' AND codtab = vintipart.maecte.canton) AS ciudad
					      FROM xbase.movcte2
					      INNER JOIN xbase.maecte ON codcte43=codcte01
					      WHERE tipodoc43 IN ('53')  AND ((fecdoc43 >= 'xfinicio'  AND fecdoc43 <= 'xffin') or (fecdoc43 >= 'yfinicioa'  AND fecdoc43 <= 'yffina')
                                or (fecdoc43 >= 'zfinicioa'  AND fecdoc43 <= 'zffina')) AND cvanulado43<>'S' AND tipoNC43<>'P' and ocurren43 in ('00','0000')
                              and case when '0'='xclie' then true else codcte43 in ('xclie') end";

        $queryncmatriz=  str_replace('xbase',$bodega,$queryncmatriz);
        $queryncmatriz=  str_replace('xfinicio',$inicio,$queryncmatriz);
        $queryncmatriz=  str_replace('xffin',$fin,$queryncmatriz);
        $queryncmatriz=  str_replace('xmarc',$marca,$queryncmatriz);
        $queryncmatriz=  str_replace('xprod',$producto,$queryncmatriz);
        $queryncmatriz=  str_replace('xvend',$vendedor,$queryncmatriz);
        $queryncmatriz=  str_replace('xclie',$cliente,$queryncmatriz);

        $queryncmatriz=  str_replace('yfinicioa',$inicioa,$queryncmatriz);
        $queryncmatriz=  str_replace('yffina',$fina,$queryncmatriz);

        $queryncmatriz=  str_replace('zfinicioa',$inicioa1,$querync);
        $queryncmatriz=  str_replace('zffina',$fina1,$querync);


        $query=$queryncmatriz;


        return $query;
    }


    public function grupoConsulta($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente)
    {


        return $query;
    }





}
