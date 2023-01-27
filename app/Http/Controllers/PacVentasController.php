<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\FechaSemana;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use DateTime;
use Carbon\Carbon;

class PacVentasController extends Controller
{
    use FormatResponseTrait;


    private $sqlgen="";

    public function __construct()
    {
        //$this->middleware('auth:admin');
    }


    public function ventasSemana(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $fechasreporte=$this->generaFechasSemana($inicio, $fin);
        try{
            $sql="SELECT  codigo,articulo,categoria,marca AS marca,";

            foreach ($fechasreporte as $fec) {
                $sql = $sql."sem_".$fec->numero.",";
            }

            $sql = $sql."total,stock,(stock - total) AS saldo FROM( ";

            $sql =$sql."SELECT  codigo,
	                        articulo,
                            categoria,
                            marca AS marca,";

            foreach ($fechasreporte as $fec) {
                $sql = $sql."SUM(IF(fecha>='".$fec->finicio."' and fecha <='".$fec->ffinal."' ,  cantidad-cantidadnc, 0)) AS sem_".$fec->numero.",";
            }

            $sql = $sql." SUM(cantidad-cantidadnc) AS total
                        FROM (

                                         SELECT codprod01 AS codigo,
	                                         desprod01 AS articulo,
	                                         fecmov03 AS fecha,
	                                         NOCOMP03 AS documento,
	                                         IF (tipotra03='80',cantid03,0) AS cantidad,
	                                         IF (tipotra03='80',precvta03 - descvta03-desctotvta03,0) AS vtaNeta,
	                                         IF (tipotra03!='80',cantid03,0) AS cantidadnc,
	                                         IF (tipotra03!='80',(precvta03 - descvta03-desctotvta03),0) AS vtaNetanc,
	                                         ca.desccate AS categoria,
	                                         (SELECT DISTINCT desccate FROM jcev.categorias AS cat WHERE cat.tipocate='02' AND cat.codcate=ca.codcatep) AS categoriap,
	                                         (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca
                                          FROM jcev.movpro
                                          INNER JOIN jcev.maepro ON codprod03 = codprod01
                                          LEFT JOIN jcev.categorias AS ca ON  catprod01=codcate
                                          WHERE tipotra03 IN ('80','22','23') AND cvanulado03 <>'S' AND fecmov03 >= xinicio  AND fecmov03 <= xfin AND  tipprod01='S'
                                          UNION ALL
                                         SELECT codprod01 AS codigo,
	                                         desprod01 AS articulo,
	                                         fecmov03 AS fecha,
	                                         NOCOMP03 AS documento,
	                                         IF (tipotra03='80',cantid03,0) AS cantidad,
	                                         IF (tipotra03='80',precvta03 - descvta03-desctotvta03,0) AS vtaNeta,
	                                         IF (tipotra03!='80',cantid03,0) AS cantidadnc,
	                                         IF (tipotra03!='80',(precvta03 - descvta03-desctotvta03),0) AS vtaNetanc,
	                                         ca.desccate AS categoria,
	                                         (SELECT DISTINCT desccate FROM jcev.categorias AS cat WHERE cat.tipocate='02' AND cat.codcate=ca.codcatep) AS categoriap,
	                                         (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca
                                          FROM jcevgye1.movpro
                                          INNER JOIN jcevgye1.maepro  ON codprod03 = codprod01
                                          LEFT JOIN jcev.categorias AS ca ON  catprod01=codcate
                                          WHERE tipotra03 IN ('80','22','23') AND cvanulado03 <>'S' AND fecmov03 >= xinicio  AND fecmov03 <= xfin AND  tipprod01='S'
                                          UNION ALL
                                          SELECT codprod01 AS codigo,
	                                         desprod01 AS articulo,
	                                         fecmov03 AS fecha,
	                                         NOCOMP03 AS documento,
	                                         IF (tipotra03='80',cantid03,0) AS cantidad,
	                                         IF (tipotra03='80',precvta03 - descvta03-desctotvta03,0) AS vtaNeta,
	                                         IF (tipotra03!='80',cantid03,0) AS cantidadnc,
	                                         IF (tipotra03!='80',(precvta03 - descvta03-desctotvta03),0) AS vtaNetanc,
	                                         ca.desccate AS categoria,
	                                         (SELECT DISTINCT desccate FROM jcev.categorias AS cat WHERE cat.tipocate='02' AND cat.codcate=ca.codcatep) AS categoriap,
	                                         (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca
                                          FROM jcevuio1.movpro
                                          INNER JOIN jcevuio1.maepro   ON codprod03 = codprod01
                                          LEFT JOIN jcev.categorias AS ca ON  catprod01=codcate
                                          WHERE tipotra03 IN ('80','22','23') AND cvanulado03 <>'S' AND fecmov03 >= xinicio  AND fecmov03 <= xfin AND  tipprod01='S'
                                          UNION ALL
                                          SELECT codprod01 AS codigo,
	                                         desprod01 AS articulo,
	                                         fecmov03 AS fecha,
	                                         NOCOMP03 AS documento,
	                                         IF (tipotra03='80',cantid03,0) AS cantidad,
	                                         IF (tipotra03='80',precvta03 - descvta03-desctotvta03,0) AS vtaNeta,
	                                         IF (tipotra03!='80',cantid03,0) AS cantidadnc,
	                                         IF (tipotra03!='80',(precvta03 - descvta03-desctotvta03),0) AS vtaNetanc,
	                                         ca.desccate AS categoria,
	                                         (SELECT DISTINCT desccate FROM jcev.categorias AS cat WHERE cat.tipocate='02' AND cat.codcate=ca.codcatep) AS categoriap,
	                                         (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca
                                          FROM jcevgyeassem.movpro
                                          INNER JOIN jcevgyeassem.maepro ON codprod03 = codprod01
                                          LEFT JOIN jcev.categorias AS ca ON  catprod01=codcate
                                          WHERE tipotra03 IN ('80','22','23') AND cvanulado03 <>'S' AND fecmov03 >= xinicio  AND fecmov03 <= xfin AND  tipprod01='S'
                                          UNION ALL
                                          SELECT codprod01 AS codigo,
	                                         desprod01 AS articulo,
	                                         fecmov03 AS fecha,
	                                         NOCOMP03 AS documento,
	                                         IF (tipotra03='80',cantid03,0) AS cantidad,
	                                         IF (tipotra03='80',precvta03 - descvta03-desctotvta03,0) AS vtaNeta,
 	                                         IF (tipotra03!='80',cantid03,0) AS cantidadnc,
	                                         IF (tipotra03!='80',(precvta03 - descvta03-desctotvta03),0) AS vtaNetanc,
	                                         ca.desccate AS categoria,
	                                         (SELECT DISTINCT desccate FROM jcev.categorias AS cat WHERE cat.tipocate='02' AND cat.codcate=ca.codcatep) AS categoriap,
	                                         (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca
                                          FROM jcevconsigvirt.movpro
                                          INNER JOIN jcevconsigvirt.maepro ON codprod03 = codprod01
                                          LEFT JOIN jcev.categorias AS ca ON  catprod01=codcate
                                          WHERE tipotra03 IN ('80','22','23') AND cvanulado03 <>'S' AND fecmov03 >= xinicio  AND fecmov03 <= xfin AND  tipprod01='S'
                                           UNION ALL
                                          SELECT codprod01 AS codigo,
	                                         desprod01 AS articulo,
	                                         fecmov03 AS fecha,
	                                         NOCOMP03 AS documento,
	                                         IF (tipotra03='80',cantid03,0) AS cantidad,
	                                         IF (tipotra03='80',precvta03 - descvta03-desctotvta03,0) AS vtaNeta,
 	                                         IF (tipotra03!='80',cantid03,0) AS cantidadnc,
	                                         IF (tipotra03!='80',(precvta03 - descvta03-desctotvta03),0) AS vtaNetanc,
	                                         ca.desccate AS categoria,
	                                         (SELECT DISTINCT desccate FROM jcev.categorias AS cat WHERE cat.tipocate='02' AND cat.codcate=ca.codcatep) AS categoriap,
	                                         (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca
                                          FROM jcevgye10.movpro
                                          INNER JOIN jcevgye10.maepro ON codprod03 = codprod01
                                          LEFT JOIN jcev.categorias AS ca ON  catprod01=codcate
                                          WHERE tipotra03 IN ('80','22','23') AND cvanulado03 <>'S' AND fecmov03 >= xinicio  AND fecmov03 <= xfin AND  tipprod01='S'
                                          UNION ALL
                                         SELECT codprod01 AS codigo,
	                                         desprod01 AS articulo,
	                                         fecmov03 AS fecha,
	                                         NOCOMP03 AS documento,
	                                         IF (tipotra03='80',cantid03,0) AS cantidad,
	                                         IF (tipotra03='80',precvta03 - descvta03-desctotvta03,0) AS vtaNeta,
	                                         IF (tipotra03!='80',cantid03,0) AS cantidadnc,
	                                         IF (tipotra03!='80',(precvta03 - descvta03-desctotvta03),0) AS vtaNetanc,
	                                         ca.desccate AS categoria,
	                                         (SELECT DISTINCT desccate FROM jcev.categorias AS cat WHERE cat.tipocate='02' AND cat.codcate=ca.codcatep) AS categoriap,
	                                         (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca
                                          FROM jcevcuenca1.movpro
                                          INNER JOIN jcevcuenca1.maepro  ON codprod03 = codprod01
                                          LEFT JOIN jcev.categorias AS ca ON  catprod01=codcate
                                          WHERE tipotra03 IN ('80','22','23') AND cvanulado03 <>'S' AND fecmov03 >= xinicio  AND fecmov03 <= xfin AND  tipprod01='S'
                                         UNION ALL
                                         SELECT codprod01 AS codigo,
	                                         desprod01 AS articulo,
	                                         fecmov03 AS fecha,
	                                         NOCOMP03 AS documento,
	                                         IF (tipotra03='80',cantid03,0) AS cantidad,
	                                         IF (tipotra03='80',precvta03 - descvta03-desctotvta03,0) AS vtaNeta,
	                                         IF (tipotra03!='80',cantid03,0) AS cantidadnc,
	                                         IF (tipotra03!='80',(precvta03 - descvta03-desctotvta03),0) AS vtaNetanc,
	                                         ca.desccate AS categoria,
	                                         (SELECT DISTINCT desccate FROM jcev.categorias AS cat WHERE cat.tipocate='02' AND cat.codcate=ca.codcatep) AS categoriap,
	                                         (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca
                                          FROM jcevstecvir.movpro
                                          INNER JOIN jcevstecvir.maepro  ON codprod03 = codprod01
                                          LEFT JOIN jcev.categorias AS ca ON  catprod01=codcate
                                           WHERE tipotra03 IN ('80','22','23') AND cvanulado03 <>'S' AND fecmov03 >= xinicio  AND fecmov03 <= xfin AND  tipprod01='S'

                                        UNION ALL
                                         SELECT codprod01 AS codigo,
	                                         desprod01 AS articulo,
	                                         fecmov03 AS fecha,
	                                         NOCOMP03 AS documento,
	                                         IF (tipotra03='80',cantid03,0) AS cantidad,
	                                         IF (tipotra03='80',precvta03 - descvta03-desctotvta03,0) AS vtaNeta,
	                                         IF (tipotra03!='80',cantid03,0) AS cantidadnc,
	                                         IF (tipotra03!='80',(precvta03 - descvta03-desctotvta03),0) AS vtaNetanc,
	                                         ca.desccate AS categoria,
	                                         (SELECT DISTINCT desccate FROM jcev.categorias AS cat WHERE cat.tipocate='02' AND cat.codcate=ca.codcatep) AS categoriap,
	                                         (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab = '4530' AND codtab <> '' AND codtab = marca01) AS marca
                                          FROM jcevcuenca2.movpro
                                          INNER JOIN jcevcuenca2.maepro  ON codprod03 = codprod01
                                          LEFT JOIN jcev.categorias AS ca ON  catprod01=codcate
                                          WHERE tipotra03 IN ('80','22','23') AND cvanulado03 <>'S' AND fecmov03 >= xinicio  AND fecmov03 <= xfin AND  tipprod01='S'
                                          ORDER BY codigo ASC
                                        ) a
                                        GROUP BY a.codigo,a.articulo,a.categoria,a.marca
                                        ORDER BY total DESC
                                         ) c

                                          INNER JOIN
                                          (
	                                        SELECT codprod01,SUM(cantact01) AS stock
	                                        FROM
	                                        (
	                                        SELECT codprod01, cantact01
	                                        FROM jcev.maepro
	                                        UNION
	                                        SELECT codprod01, cantact01
	                                        FROM jcevcuenca2.maepro
	                                        UNION
	                                        SELECT codprod01, cantact01
	                                        FROM jcevgye1.maepro
	                                        UNION
	                                        SELECT codprod01, cantact01
	                                        FROM jcevuio1.maepro
	                                        UNION
	                                        SELECT codprod01, cantact01
	                                        FROM jcevconsigvirt.maepro
	                                        UNION
	                                        SELECT codprod01, cantact01
	                                        FROM jcevstecvir.maepro
	                                        UNION
	                                        SELECT codprod01, cantact01
	                                        FROM jcevgyeassem.maepro
	                                        UNION
	                                        SELECT codprod01, cantact01
	                                        FROM jcevcuenca1.maepro
	                                        UNION
	                                        SELECT codprod01, cantact01
	                                        FROM jcevgye10.maepro
	                                        ORDER BY codprod01
	                                        ) a
	                                        GROUP BY a.codprod01
	                                        ORDER BY a.codprod01
                                        ) b ON c.codigo=b.codprod01 ORDER BY categoria ASC,marca,codigo";

            $sql=  str_replace('xinicio',"'$inicio'",$sql);
            $sql=  str_replace('xfin',"'$fin'",$sql);

            $list = DB::connection('mysqlpac')->select($sql);
            //return $this->getOk($list);
            $data = array(
                    'err' => 'false',
                    'status' => 'success',
                    'message' => 'Cargado correctamente fsigu',
                    'fechas' => $fechasreporte,
                    'data' => $list
                );

                return $this->getOk($data);

        } catch (\Exception $e) {
            return $this->insertErrCustom($input, $e->getMessage());
        }


    }


    public function generaFechasSemana($inicio,$fin)
    {
        //$input = $request->all();
        //$finicio=$request['finicio'].' 00:00:00';
        //$finicio=$request['finicio'];
        //$ffin=$request['ffin'].' 23:59:00';
        $finicio=Carbon::parse($inicio);
        $ffin=Carbon::parse($fin);

        $fin=false;
        $num = 1;
        $fechas=[];
        $ano =$finicio->year;
        $mes =$finicio->month;

        //$ano =  date("Y", strtotime($finicio));
        //$mes =  date("m", strtotime($finicio));
        $primerLunes =$this->PrimerLunesDelMes($ano, $mes);

        $number = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
        //$ultimoDiaMes = new DateTime();
        //$ultimoDiaMes->setDate($ano, $mes, $number);
        $ultimoDiaMes = Carbon::create($ano,$mes, $number, 0, 0, 0);

        $primerDia = Carbon::create($ano,$mes, 1, 0, 0, 0);
        $primerDiaDate = Carbon::create($ano,$mes, $primerLunes, 0, 0, 0);

        if ($primerDiaDate > $primerDia){
            $ffinal=$primerDia->copy()->addDays($primerLunes-2);;
            $fecha=new FechaSemana();
            $fecha->numero=$num;
            $fecha->finicio=$primerDia->format('Y-m-d').' 00:00:00';
            $fecha->ffinal=$ffinal->format('Y-m-d').' 23:59:00';
            array_push($fechas,$fecha);
            $num++;
        }

        while ($fin == false){

            $finicio = Carbon::create($ano,$mes, $primerLunes, 0, 0, 0);
            $ffinal=$finicio->copy()->addDays(6);
            $fecha=new FechaSemana();
            $fecha->numero=$num;
            $fecha->finicio=$finicio->format('Y-m-d').' 00:00:00';
            $fecha->ffinal=$ffinal->copy()->format('Y-m-d').' 23:59:00';
            //$fechas[$num]=$fecha;
            array_push($fechas,$fecha);
            $num++;

            $ffinal1=$ffinal->copy();
            $ffinal1->addDays(6);
            if ($ffinal1 >= $ultimoDiaMes){
                $fin = true;
            }

            //$primerLunes = strtotime($ffinal."+ 1 days");
            $ffinal->addDays(1);
            $primerLunes = $ffinal->day;
        }


        if ($ffinal <= $ultimoDiaMes){
            //$ffinal->addDays(1);
            $finicio=$ffinal->copy();
            $fecha=new FechaSemana();
            $fecha->numero=$num;
            $fecha->finicio=$finicio->format('Y-m-d').' 00:00:00';
            $fecha->ffinal=$ultimoDiaMes->format('Y-m-d').' 23:59:00';
            //$fechas[$num]=$fecha;
            array_push($fechas,$fecha);
        }

        //return $this->getOk($fechas);
        return $fechas;

    }

    public function PrimerLunesDelMes($anio,$mes){
        //$primerLunesDelMes = new DateTime(anio, mes, 1);
        //$primerLunesDelMes = date("$anio-$mes-01");
        $primerLunesDelMes = Carbon::create($anio,$mes, 1, 0, 0, 0);
        $diasemana=$primerLunesDelMes->format("l");
        //$diasemana=date("l",strtotime($primerLunesDelMes));

        while ($diasemana != "Monday"){
            //$primerLunesDelMes = date("Y-m-d",strtotime($primerLunesDelMes."+ 1 days"));
            //$primerLunesDelMes = strtotime($primerLunesDelMes."+ 1 days");
            $primerLunesDelMes =$primerLunesDelMes->addDays(1);
            $diasemana=$primerLunesDelMes->format("l");
            //$diasemana=date("Y",$primerLunesDelMes);
        }

        return $primerLunesDelMes->day;
    }




}
