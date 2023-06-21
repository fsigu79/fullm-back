<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\SeriePac;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class TodoMotoPyGController extends Controller
{
    use FormatResponseTrait;

    private $sqlgen="select distinct  p.cod,p.des,p.pad,p.mov,coalesce(s.r_ene,0) as r_ene,
                    coalesce(s.r_feb,0) as r_feb,
                    coalesce(s.r_mar,0) as r_mar,
                    coalesce(s.r_abr,0) as r_abr,
                    coalesce(s.r_may,0) as r_may,
                    coalesce(s.r_jun,0) as r_jun,
                    coalesce(s.r_jul,0) as r_jul,
                    coalesce(s.r_ago,0) as r_ago,
                    coalesce(s.r_sep,0) as r_sep,
                    coalesce(s.r_oct,0) as r_oct,
                    coalesce(s.r_nov,0) as r_nov,
                    coalesce(s.r_dic,0) as r_dic,
                    coalesce(s.r_tot,0) as r_tot
            from(
                select codcate as cod,desccate as des,codcatep as pad,0 as mov
                from todomoto.categorias
                where tipocate='01' and codcate!='0' and (codcate like '4%' or  codcate like '5%' )
                union
                select ctamaecon,nomcta,catctamaecon,1 as mov
                from todomoto.maecon where (ctamaecon like '4%' or  ctamaecon like '5%' ) ) as p
            left join

                (select t1.ctamaecon,nomcta,
                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM todomoto.movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=1 and year(fechahis)='xano' AND dethis  NOT LIKE 'CIERRE DE EJERCICIO%'
                            ) as r_ene,

                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM todomoto.movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=2 and year(fechahis)='xano'
                            ) as r_feb,

                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM todomoto.movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=3 and year(fechahis)='xano') as r_mar,
                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM todomoto.movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=4 and year(fechahis)='xano') as r_abr,
                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM todomoto.movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=5 and year(fechahis)='xano') as r_may,
                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM todomoto.movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=6 and year(fechahis)='xano') as r_jun,
                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM todomoto.movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=7 and year(fechahis)='xano') as r_jul,
                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM todomoto.movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=8 and year(fechahis)='xano') as r_ago,
                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM todomoto.movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=9 and year(fechahis)='xano') as r_sep,
                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM todomoto.movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=10 and year(fechahis)='xano') as r_oct,
                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM todomoto.movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=11 and year(fechahis)='xano') as r_nov,
                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM todomoto.movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)=12 and year(fechahis)='xano') as r_dic,
                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                            FROM todomoto.movcon
                            WHERE ctahiscon=t1.ctamaecon and year(fechahis)='xano'  AND dethis  NOT LIKE 'CIERRE DE EJERCICIO%') as r_tot
                from todomoto.maecon t1
                where (ctamaecon like '4%' or  ctamaecon like '5%' )
                ) as s
                on p.cod=s.ctamaecon
                order by cod";



   private $sqlcompara="select distinct  p.cod,p.des,p.pad,p.mov,coalesce(s.anterior,0) as anterior,
                    coalesce(s.actual,0) as actual
            from(
                select codcate as cod,desccate as des,codcatep as pad,0 as mov
                from todomoto.categorias
                where tipocate='01' and codcate!='0' and (codcate like '4%' or  codcate like '5%' )
                union
                select ctamaecon,nomcta,catctamaecon,1 as mov
                from todomoto.maecon where (ctamaecon like '4%' or  ctamaecon like '5%' ) ) as p
            left join

                (select t1.ctamaecon,nomcta,
                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM todomoto.movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)='xmesa' and year(fechahis)='xanoa' AND dethis  NOT LIKE 'CIERRE DE EJERCICIO%'
                            ) as anterior,

                            (select ifnull(sum(if(SUBSTR(t1.ctamaecon,1,1)='4',if(db1cr2his='2',valorhis,valorhis*-1),if(db1cr2his='2',valorhis*-1,valorhis))),0)
                             FROM todomoto.movcon
                             WHERE ctahiscon=t1.ctamaecon and MONTH(fechahis)='xmes' and year(fechahis)='xano'  AND dethis  NOT LIKE 'CIERRE DE EJERCICIO%'
                            ) as actual
                from todomoto.maecon t1
                where (ctamaecon like '4%' or  ctamaecon like '5%' )
                ) as s
                on p.cod=s.ctamaecon
                order by cod";

    public function __construct()
    {
        //$this->middleware('auth:admin');
    }


    public function balanceByGTodoMoto(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $sql='';
        $anio=$request['anio'];

        $sql=  str_replace('xano',$anio,$this->sqlgen);
        //$sql=  str_replace('xfinicio',$inicio,$sql);

        $list = DB::connection('mysqlpac')->select($sql);
        //elimina datos de la tabla tmporal
        $elimina = DB::select("delete from tmp_balance_horizontal_pyg_todomoto");

        foreach ($list as $detalle) {
            $results=DB::select('SELECT balance_pyg_grabar_todomoto(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',[
                            $detalle->cod,
                            $detalle->des,
                            $detalle->pad,
                            $detalle->mov,
                            $detalle->r_ene,
                            $detalle->r_feb,
                            $detalle->r_mar,
                            $detalle->r_abr,
                            $detalle->r_may,
                            $detalle->r_jun,
                            $detalle->r_jul,
                            $detalle->r_ago,
                            $detalle->r_sep,
                            $detalle->r_oct,
                            $detalle->r_nov,
                            $detalle->r_dic,
                            $detalle->r_tot
                            ]);

        }

        $balance=DB::select('SELECT * from balance_pyg_reporte_todomoto('.$anio.')');
        /*$balance=DB::select('select id,
                                codigo as cod,
                                cuenta as des,
                                padre as pad,
                                movimiento as mov,
                                enero as r_ene,
                                febrero as r_feb,
                                marzo as r_mar,
                                abril as r_abr,
                                mayo as r_may,
                                junio as r_jun,
                                julio as r_jul,
                                agosto as r_ago,
                                septiembre as r_sep,
                                octubre as r_oct,
                                noviembre as r_nov,
                                diciembre as r_dic,
                                total as r_tot
                            from tmp_balance_horizontal_pyg
                            where total!=0
                            order by codigo');*/
        return $this->getOk($balance);
    }


    public function balanceByGCompara(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $mesini=$request['mini'];
        $mesfin=$request['mfin'];
        $aini=$request['aini'];
        $afin=$request['afin'];
        $anio='2020';
        $sql=  str_replace('xmesa',$mesini,$this->sqlcompara);
        $sql=  str_replace('xanoa',$aini,$sql);
        $sql=  str_replace('xmes',$mesfin,$sql);
        $sql=  str_replace('xano',$afin,$sql);

        $list = DB::connection('mysqlpac')->select($sql);
        //elimina datos de la tabla tmporal
        $elimina = DB::select("delete from tmp_balhor_pyg_compara");

        foreach ($list as $detalle) {
            $results=DB::select('SELECT balance_pyg_compara_grabar(?,?,?,?,?,?)',[
                            $detalle->cod,
                            $detalle->des,
                            $detalle->pad,
                            $detalle->mov,
                            $detalle->anterior,
                            $detalle->actual
                            ]);

        }

        $balance=DB::select('SELECT * from balance_pyg_compara_reporte('.$anio.')');
        return $this->getOk($balance);
    }

    public function presupuestoPacCompara(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $sql='';
        $anio=$request['anio'];
        $anioa=$request['anioa'];

        $sql=  str_replace('xanio',$anio,$this->sqlcompara);
        $sql=  str_replace('yanioa',$anioa,$sql);
        //$sql=  str_replace('xfinicio',$inicio,$sql);

        $list = DB::connection('mysqlpac')->select($sql);

        return $this->getOk($list);
    }




}
