<?php

namespace App\Http\Controllers;

use App\Exports\ClientExport;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Customer;
use App\Models\SqlModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use PDF;

class ClientesPacDetalleController extends Controller
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
  					        (SELECT DISTINCT nomtab FROM fullm.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca,
	 				        novend31 AS vencod,
  					        (SELECT nomtab FROM fullm.maetab WHERE numtab='73' AND codtab =novend31) AS vendedor,
					        catcte01 AS catcod,
  					        (SELECT a.desccate AS categoria FROM fullm.categorias a INNER JOIN  fullm.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate
               FROM xbase.movpro
               INNER JOIN ybase1.maepro ON codprod03 = codprod01
               INNER JOIN zbase1.maefac ON NOCOMP03=nofact31 AND cvanulado31!=9
               INNER JOIN fullm.maecte ON nocte31=codcte01
               WHERE tipotra03 IN ('80') AND cvanulado03 <>'S' AND fecmov03 >= 'xfinicio'  AND fecmov03 <= 'xffin' AND  tipprod01='S'
                    and case when '0'='xmarc' then true else marca01 in ('xmarc') end
                    and case when '0'='xprod' then true else codprod01 in ('xprod') end
                    and case when '0'='xvend' then true else novend31 in ('xvend') end
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
						     (SELECT DISTINCT nomtab FROM fullm.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca,
						      numvencob43 AS vencod,
						      (SELECT nomtab FROM fullm.maetab WHERE numtab='73' AND codtab=numvencob43) AS vendedor,
						      catcte01 AS catcod,
						      (SELECT a.desccate AS categoria FROM fullm.categorias a INNER JOIN  fullm.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate
					      FROM fullm.movcte
					      INNER JOIN fullm.maecte ON codcte43=codcte01
					      INNER JOIN xbase.movpro  ON NOCOMP03=numdoc43 AND tipotra03 IN ('22') AND cvanulado03 <>'S'
					      INNER JOIN xbase.maepro ON codprod03 = codprod01 AND  tipprod01='S'  AND statuspro01='S'
					      WHERE tipodoc43 IN ('53')  AND fecdoc43 >= 'xfinicio'  AND fecdoc43 <= 'xffin' AND cvanulado43<>'S' AND tipoNC43='P' and ocurren43 in ('00','0000')
                                and case when '0'='xmarc' then true else marca01 in ('xmarc') end
                                and case when '0'='xprod' then true else codprod01 in ('xprod') end
                                and case when '0'='xvend' then true else numvencob43 in ('xvend') end
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
						     (SELECT DISTINCT nomtab FROM fullm.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca,
						      numvencob43 AS vencod,
						      (SELECT nomtab FROM fullm.maetab WHERE numtab='73' AND codtab=numvencob43) AS vendedor,
						      catcte01 AS catcod,
						      (SELECT a.desccate AS categoria FROM fullm.categorias a INNER JOIN  fullm.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate
					      FROM fullm.movcte2
					      INNER JOIN fullm.maecte ON codcte43=codcte01
					      INNER JOIN xbase.movpro  ON NOCOMP03=numdoc43 AND tipotra03 IN ('22') AND cvanulado03 <>'S'
					      INNER JOIN xbase.maepro ON codprod03 = codprod01 AND  tipprod01='S'  AND statuspro01='S'
					      WHERE tipodoc43 IN ('53')  AND fecdoc43 >= 'xfinicio'  AND fecdoc43 <= 'xffin' AND cvanulado43<>'S' AND tipoNC43='P'  and ocurren43 in ('00','0000')
                                and case when '0'='xmarc' then true else marca01 in ('xmarc') end
                                and case when '0'='xprod' then true else codprod01 in ('xprod') end
                                and case when '0'='xvend' then true else numvencob43 in ('xvend') end
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
               $sql=$this->generaQueryVentas('fullm',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmcuenca2',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmcuenca1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmgye1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmgye10',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmuio1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmconsigvirt',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmgyeassem',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmconsigvirt',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'SI');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmstecvir',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmcuenca1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'SI');
                // select de notas de credito
                $sqlnc=$this->generaQueryNC('fullm',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmconsigvirt',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmcuenca2',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmcuenca1',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmgye1',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmgye10',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmuio1',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmgyeassem',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmstecvir',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                if ($producto='0'){
                    $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNCMatriz('fullm',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                }


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
            //return $this->getOk('trueeeee');
            $sql=$sql.' UNION ALL SELECT agencia,codigocliente,cliente,desfac,
					            codigo, articulo,
	                            tipodoc,documento,fecha,
	                            cantidad,costotal,
	                            vtatotal,desproducto,
	                            descliente,
	                            IF(codigo="-",net,vtaNeta) AS vtaNeta,
	                            marcod,marca,
					            vencod,vendedor,
					            catcod,cate
                        FROM ( '.$sqlnc.') a';
        }
        else
        {
            //return $this->getOk('falseeee');
            $sql=$sql.' UNION ALL SELECT agencia,codigocliente,cliente,desfac,
					            codigo, articulo,
	                            tipodoc,documento,fecha,
	                            cantidad,costotal,
	                            vtatotal,desproducto,
	                            descliente,
	                            IF(codigo="-",net,vtaNeta) AS vtaNeta,
	                            marcod,marca,
					            vencod,vendedor,
					            catcod,cate
                        FROM ( '.$sqlnc.') a';
        }



        //return $this->getOk($sql);
         //fsigu sqls

         $box = new SqlModel();
            $box->sql= $sql;
            $box->sql1=$sql;
            $box->save();

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
        $marca=isset($request['marca_id']) ?$request['marca_id']:'0';
        $producto=isset($request['producto_id']) ?$request['producto_id']:'0';
        $vendedor=isset($request['vendedor_id']) ?$request['vendedor_id']:'0';
        $cliente=isset($request['cliente_id']) ?$request['cliente_id']:'0';
        $unidades=isset($request['unidades']) ?$request['unidades']:false;
        $sql='';
        $sqlnc='';



        if ($bodega=='todas'){
                // select de ventas
                $sql=$this->generaQueryVentas('fullm',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmcuenca2',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmcuenca1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmgye1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmgye10',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmuio1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmconsigvirt',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmgyeassem',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmconsigvirt',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'SI');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmstecvir',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmcuenca1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'SI');

                // select de notas de credito
                $sqlnc=$this->generaQueryNC('fullm',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmconsigvirt',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmcuenca2',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmcuenca1',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmgye1',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmgye10',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmuio1',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmgyeassem',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmstecvir',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNCMatriz('fullm',$inicio,$fin,$marca,$producto,$vendedor,$cliente);


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
            $sql=$sql.' UNION ALL SELECT agencia,codigocliente,cliente,desfac,
					            codigo, articulo,
	                            tipodoc,documento,fecha,
	                            cantidad,costotal,
	                            vtatotal,desproducto,
	                            descliente,
	                            IF(codigo="-",net,vtaNeta) AS vtaNeta,
	                            marcod,marca,
					            vencod,vendedor,
					            catcod,cate
                        FROM ( '.$sqlnc.') a';
        }
        else
        {
            $sql=$sql.' UNION ALL SELECT agencia,codigocliente,cliente,desfac,
					            codigo, articulo,
	                            tipodoc,documento,fecha,
	                            cantidad,costotal,
	                            vtatotal,desproducto,
	                            descliente,
	                            IF(codigo="-",net,vtaNeta) AS vtaNeta,
	                            marcod,marca,
					            vencod,vendedor,
					            catcod,cate
                        FROM ( '.$sqlnc.') a ';
        }



            $box = new SqlModel();
            $box->sql= $sql;
            $box->sql1=$sql;
            $box->save();
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
                $sql=$this->generaQueryVentas('fullm',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmcuenca2',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmcuenca1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmgye1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmgye10',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmuio1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmconsigvirt',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmgyeassem',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmconsigvirt',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'SI');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmstecvir',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmcuenca1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'SI');

                // select de notas de credito
                $sqlnc=$this->generaQueryNC('fullm',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmconsigvirt',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmcuenca2',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmcuenca1',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmgye1',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmgye10',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmuio1',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmgyeassem',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmstecvir',$inicio,$fin,$marca,$producto,$vendedor,$cliente);

        }
        else
        {
            // select de ventas
            $sql=$this->generaQueryVentas($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
            // select de notas de credito
            $sqlnc=$this->generaQueryNC($bodega,$inicio,$fin,$marca,$producto,$vendedor,$cliente);
        }
        // IFNULL((SELECT cantact01 FROM fullmconsigvirt.maepro WHERE codigo=fullmconsigvirt.maepro.codprod01),0) +
        if ($unidades=="true")
        {
            $sql=$sql.' UNION ALL SELECT agencia,codigocliente,cliente,desfac,
					            codigo, articulo,
	                            tipodoc,documento,fecha,
	                            cantidad,costotal,
	                            vtatotal,desproducto,
	                            descliente,
	                            IF(codigo="-",net,vtaNeta) AS vtaNeta,
	                            marcod,marca,
					            vencod,vendedor,
					            catcod,cate
                        FROM ( '.$sqlnc.') a ';
        }
        else
        {
            //IFNULL((SELECT cantact01 FROM fullmconsigvirt.maepro WHERE codigo=fullmconsigvirt.maepro.codprod01),0) +
            $sql=$sql.' UNION ALL SELECT agencia,codigocliente,cliente,desfac,
					            codigo, articulo,
	                            tipodoc,documento,fecha,
	                            cantidad,costotal,
	                            vtatotal,desproducto,
	                            descliente,
	                            IF(codigo="-",net,vtaNeta) AS vtaNeta,
	                            marcod,marca,
					            vencod,vendedor,
					            catcod,cate
                        FROM ( '.$sqlnc.') a';

        }

        //echo $sql;
        //fsigu sqls
         /*$box = new SqlModel();
            $box->sql= $sql;
            $box->sql1=$sql;
            $box->save();*/
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
        $marca=isset($request['marca_id']) ?$request['marca_id']:'0';
        $producto=isset($request['producto_id']) ?$request['producto_id']:'0';
        $vendedor=isset($request['vendedor_id']) ?$request['vendedor_id']:'0';
        $cliente=isset($request['cliente_id']) ?$request['cliente_id']:'0';
        $unidades=isset($request['unidades']) ?$request['unidades']:false;
        $sql='';
        $sqlnc='';



        if ($bodega=='todas'){
                // select de ventas
                $sql=$this->generaQueryVentas('fullm',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmcuenca2',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmcuenca1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmgye1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmgye10',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmuio1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmconsigvirt',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmgyeassem',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmconsigvirt',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'SI');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmstecvir',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'NO');
                $sql=$sql.' UNION ALL '.$this->generaQueryVentas('fullmcuenca1',$inicio,$fin,$marca,$producto,$vendedor,$cliente,'SI');


                // select de notas de credito
                $sqlnc=$this->generaQueryNC('fullm',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmconsigvirt',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmcuenca2',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmcuenca1',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmgye1',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmgye10',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmuio1',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmgyeassem',$inicio,$fin,$marca,$producto,$vendedor,$cliente);
                $sqlnc=$sqlnc.' UNION ALL '.$this->generaQueryNC('fullmstecvir',$inicio,$fin,$marca,$producto,$vendedor,$cliente);

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
            $sql=$sql.' UNION ALL SELECT agencia,codigocliente,cliente,desfac,
					            codigo, articulo,
	                            tipodoc,documento,fecha,
	                            cantidad,costotal,
	                            vtatotal,desproducto,
	                            descliente,
	                            IF(codigo="-",net,vtaNeta) AS vtaNeta,
	                            marcod,marca,
					            vencod,vendedor,
					            catcod,cate
                        FROM ( '.$sqlnc.') a';
        }
        else
        {
            $sql=$sql.' UNION ALL SELECT agencia,codigocliente,cliente,desfac,
					            codigo, articulo,
	                            tipodoc,documento,fecha,
	                            cantidad,costotal,
	                            vtatotal,desproducto,
	                            descliente,
	                            IF(codigo="-",net,vtaNeta) AS vtaNeta,
	                            marcod,marca,
					            vencod,vendedor,
					            catcod,cate
                        FROM ( '.$sqlnc.') a';
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
        if ($bodega=='fullmconsigvirt'){
            $query= str_replace('ybase1','fullmconsigvirt',$query);
            if ($virtual1=='SI'){
                $query= str_replace('zbase1','fullmgyeassem',$query);
            }
            else{
                $query= str_replace('zbase1','fullmuio1',$query);
            }
        }

        if ($bodega=='fullmcuenca1'){
            $query= str_replace('ybase1','fullmcuenca1',$query);
            if ($virtual1=='SI'){
                $query= str_replace('zbase1','fullmgyeassem',$query);
            }
            else{
                $query= str_replace('zbase1','fullmcuenca1',$query);
            }
        }

        if ($bodega!='fullmconsigvirt' && $bodega!='fullmcuenca1'){
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

        $query=$querync.' UNION ALL '.$querync1;

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
						      (SELECT nomtab FROM fullm.maetab WHERE numtab='73' AND codtab=numvencob43) AS vendedor,
						      catcte01 AS catcod,
						      (SELECT a.desccate AS categoria FROM fullm.categorias a INNER JOIN  fullm.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate
					    FROM xbase.movcte
					    INNER JOIN xbase.maecte ON codcte43=codcte01
					    WHERE tipodoc43 IN ('53')  AND fecdoc43 >= 'xfinicio'  AND fecdoc43 <= 'xffin' AND cvanulado43<>'S' AND tipoNC43<>'P' and ocurren43 in ('00','0000')
                            and case when '0'='xclie' then true else codcte43 in ('xclie') end
                        UNION ALL
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
						      (SELECT nomtab FROM fullm.maetab WHERE numtab='73' AND codtab=numvencob43) AS vendedor,
						      catcte01 AS catcod,
						      (SELECT a.desccate AS categoria FROM  fullm.categorias a INNER JOIN   fullm.categorias b ON a.codcatep=b.codcate AND b.tipocate='03' WHERE a.tipocate='03' AND a.codcate=catcte01) AS cate
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


    public function exportLabel($id)
    {
        $sql='SELECT codprod01 as codigo,desprod01 as producto from fullm.maepro where codprod01 in("BICI308A24V","BICI89","BUZ01")';
        $order = DB::connection('mysqlpac')->select($sql);
        $customPaper = array(0,0,567.00,283.80);
        $pdf = PDF::loadView('label',compact('order')
        )->setPaper([0, 0, 141.73,283.47 ], 'landscape');

        return $pdf->stream('label1.pdf');
    }

    public function searchClientesPac(Request $request)
    {
        $input = $request->all();
        //return $this->getOk($input);
        $cod=$input['ruc'];
        $des=strtoupper($input['name']);

         if ($input['ruc']=='null' || $input['ruc']==''){
            $cod='0';
        }else{
            $cod=$input['ruc'];
        }

        if ($input['name']=='NULL' || $input['name']==''){
            $des='0';
        }else{
            $des=$input['name'];
        }

       try{

            $sql="select 1 as id,codcte01 as codigo, nomcte01 as nombre, cascte01 as ruc,'.' as apellido,
                dircte01,telcte01,if (isnull(emailaltcte01) or emailaltcte01='',emailcte01,concat(emailcte01,',',emailaltcte01)) as email
                from fullm.maecte
                where if ('".$cod."'='0',true,cascte01 like '%".$cod."%') and
                        if ('".$des."'='0',true,nomcte01 like '%".$des."%')";

            $list = DB::connection('mysqlpac')->select($sql);
            return $this->getOk($list);

        }catch(\Exception $e) {
            return $this->insertErrCustom($request, $e->getMessage());
        }
    }


    public function filterClientesPac(Request $request)
    {
        $input = $request->all();
        //return $this->getOk($input);
        $cod=$input['ruc'];
        $des=strtoupper($input['name']);

         if ($input['ruc']=='null' || $input['ruc']==''){
            $cod='0';
        }else{
            $cod=$input['ruc'];
        }

        if ($input['name']=='NULL' || $input['name']==''){
            $des='0';
        }else{
            $des=$input['name'];
        }

       try{

            $sql="select 1 as id,codcte01 as codigo, nomcte01 as nombre, cascte01 as ruc,'.' as apellido,
                dircte01,telcte01,if (isnull(emailaltcte01) or emailaltcte01='',emailcte01,concat(emailcte01,',',emailaltcte01)) as email
                from fullm.maecte
                where if ('".$cod."'='0',true,cascte01 like '%".$cod."%') or
                        if ('".$cod."'='0',true,nomcte01 like '%".$cod."%')";

            $list = DB::connection('mysqlpac')->select($sql);
            return $this->getOk($list);

        }catch(\Exception $e) {
            return $this->insertErrCustom($request, $e->getMessage());
        }
    }


    public function getClientesPacByCode(Request $request)
    {
        $input = $request->all();
        //return $this->getOk($input);
        $cod=$input['codido'];


       try{

            $sql="select 1 as id,codcte01 as codigo, nomcte01 as nombre, cascte01 as ruc,'.' as apellido,
                dircte01,telcte01,if (isnull(emailaltcte01) or emailaltcte01='',emailcte01,concat(emailcte01,',',emailaltcte01)) as email
                from fullm.maecte
                where codcte01=?";

            $list = DB::connection('mysqlpac')->select($sql,[$cod]);
            return $this->getOk($list);

        }catch(\Exception $e) {
            return $this->insertErrCustom($request, $e->getMessage());
        }
    }


     public function searchProveedorPac(Request $request)
    {
        $input = $request->all();
        //return $this->getOk($input);
        $cod=$request['ruc'];
        $des=strtoupper($input['nombres']);

        if ($input['ruc']=='null' || $input['ruc']==''){
            $cod='0';
        }else{
            $cod=$input['ruc'];
        }

        if ($input['nombres']=='null' || $input['nombres']==''){
            $des='0';
        }else{
            $des=$input['nombres'];
        }

       try{

            $sql="select 1 as id,codcte01 as codigo, nomcte01 as nombres, cascte01 as ruc,'.' as apellidos
                from fullm.maepag
                where if ('".$cod."'='0',true,cascte01 like '".$cod."%') and
                        if ('".$des."'='0',true,nomcte01 like '%".$des."%')";
            //return $this->getOk($sql);
            $list = DB::connection('mysqlpac')->select($sql);
            return $this->getOk($list);

        }catch(\Exception $e) {
            return $this->insertErrCustom($request, $e->getMessage());
        }
    }




}
