<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\SeriePac;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class PacPresupuestoController extends Controller
{
    use FormatResponseTrait;

    private $sqlgen="select t1.ctamaecon,nomcta,
                            ctacontpre,anopre,
                            valpre0,
                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=1 and year(fechahis)='xanio' AND dethis  NOT LIKE 'CIERRE DE EJERCICIO%'
                            ) as r_ene,
                            round(ifnull((select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                            FROM movcon WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=1 and year(fechahis)='xanio'
                                AND dethis  NOT LIKE 'CIERRE DE EJERCICIO%'
                            )/valpre0*100,0),2) as p_ene,
                            valpre1,
                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=2 and year(fechahis)='xanio'
                            ) as r_feb,
                            round(ifnull((select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                            FROM movcon WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=2 and year(fechahis)='xanio'
                            )/valpre1*100,0),2) as p_feb,

                            valpre2,
                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=3 and year(fechahis)='xanio') as r_mar,

                            round(ifnull((select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                            FROM movcon WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=3 and year(fechahis)='xanio'
                            )/valpre2*100,0),2) as p_mar,

                            valpre3,

                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=4 and year(fechahis)='xanio') as r_abr,

                            round(ifnull((select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                            FROM movcon WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=4 and year(fechahis)='xanio'
                            )/valpre3*100,0),2) as p_abr,

                            valpre4,

                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=5 and year(fechahis)='xanio') as r_may,

                            round(ifnull((select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                            FROM movcon WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=5 and year(fechahis)='xanio'
                            )/valpre4*100,0),2) as p_may,

                            valpre5,
                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=6 and year(fechahis)='xanio') as r_jun,

                            round(ifnull((select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                            FROM movcon WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=6 and year(fechahis)='xanio'
                            )/valpre5*100,0),2) as p_jun,

                            valpre6,

                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=7 and year(fechahis)='xanio') as r_jul,

                            round(ifnull((select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                            FROM movcon WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=7 and year(fechahis)='xanio'
                            )/valpre6*100,0),2) as p_jul,

                            valpre7,

                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=8 and year(fechahis)='xanio') as r_ago,

                            round(ifnull((select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                            FROM movcon WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=8 and year(fechahis)='xanio'
                            )/valpre7*100,0),2) as p_ago,

                            valpre8,

                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=9 and year(fechahis)='xanio') as r_sep,

                            round(ifnull((select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                            FROM movcon WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=9 and year(fechahis)='xanio'
                            )/valpre8*100,0),2) as p_sep,

                            valpre9,
                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=10 and year(fechahis)='xanio') as r_oct,

                            round(ifnull((select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                            FROM movcon WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=10 and year(fechahis)='xanio'
                            )/valpre9*100,0),2) as p_oct,

                            valpre10,
                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=11 and year(fechahis)='xanio') as r_nov,

                            round(ifnull((select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                            FROM movcon WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=11 and year(fechahis)='xanio'
                            )/valpre10*100,0),2) as p_nov,
                            valpre11,

                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=12 and year(fechahis)='xanio') as r_dic,


                            round(ifnull((select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                            FROM movcon WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=12 and year(fechahis)='xanio'
                            )/valpre11*100,0),2) as p_dic,
                            (valpre0+valpre1+valpre2+valpre3+valpre4+valpre5+valpre6+valpre7+valpre8+valpre9+valpre10+valpre11) as pretotal,
                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                            FROM movcon
                            WHERE ctahiscon=t1.ctamaecon and year(fechahis)='xanio'  AND dethis  NOT LIKE 'CIERRE DE EJERCICIO%') as r_tot,
                            round(ifnull((select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                            FROM movcon WHERE ctahiscon=t1.ctamaecon and year(fechahis)='xanio'  AND dethis  NOT LIKE 'CIERRE DE EJERCICIO%'
                            )/(valpre0+valpre1+valpre2+valpre3+valpre4+valpre5+valpre6+valpre7+valpre8+valpre9+valpre10+valpre11)*100,0),2) as p_tot

                from maecon t1,maepre t2
                where (catctamaecon like '4%' or  catctamaecon like '5%' ) and ctacontpre=ctamaecon
                    and anopre='xanio'  order by ctamaecon ASC;";

    public function __construct()
    {
        //$this->middleware('auth:admin');
    }


    public function presupuestoPac(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $sql='';
        $anio=$request['anio'];

        $sql=  str_replace('xanio',$anio,$this->sqlgen);
        //$sql=  str_replace('xfinicio',$inicio,$sql);

        $list = DB::connection('mysqlpac')->select($sql);

        return $this->getOk($list);
    }






}
