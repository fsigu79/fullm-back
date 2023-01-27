<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\SeriePac;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class TodoMotoCuposUsosController extends Controller
{
    use FormatResponseTrait;

    private $sqlgen=" SELECT codcte01 as codigo,
                            nomcte01 as cliente,
                            cascte01  as ruc,
                            vendcte01 AS vencod,
  					        (SELECT nomtab FROM todomoto.maetab WHERE numtab='73' AND codtab =vendcte01) AS vendedor,
                            sdoact01 as saldo_actual,
                            totexceso01 as exceso,
                            (sdoact01+totexceso01) as saldo_neto,
                            limcred01 as limite_credito,
                            IF(limcred01=0,0, ROUND((sdoact01+totexceso01)/limcred01,2))*100 as uso,
                            sdoeje01,
                            sdoant01,
                            acudbm01,
                            acucrm01,
                            acudbe01,
                            acucre01
                    from xbase.maecte
                    where case when '0'='xvend' then true else vendcte01 in ('xvend') end
                    order by saldo_neto desc";
                    //where fecfact31>='xfinicio' and fecfact31<='xffin'";

    public function __construct()
    {
        //$this->middleware('auth:admin');
    }


    public function cuposUsos(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $vendedor=isset($request['vendedor_id']) ?$request['vendedor_id']:'0';
        $sql='';


        try{
            $sql=$this->generaQuery('todomoto',$inicio,$fin,$vendedor);
           /* $sql=$sql.' UNION ALL '.$this->generaQuery('jcevcuenca2',$inicio,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('jcevcuenca1',$inicio,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('jcevgye1',$inicio,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('jcevgye10',$inicio,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('jcevuio1',$inicio,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('jcevconsigvirt',$inicio,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('jcevgyeassem',$inicio,$fin);
            //$sql=$sql.' UNION ALL '.$this->generaQuery('jcevconsigvirt',$inicio,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('jcevstecvir',$inicio,$fin);*/

        $list = DB::connection('mysqlpac')->select($sql);

        return $this->getOk($list);

        }catch (\Exception $e) {
            return $this->insertErrCustom($input, $e->getMessage());
        }

    }


    public function generaQuery($bodega,$inicio,$fin,$vendedor)
    {
        $query=  str_replace('xbase',$bodega,$this->sqlgen);
        $query=  str_replace('xvend',$vendedor,$query);
        //$query=  str_replace('xfinicio',$inicio,$query);
        //$query=  str_replace('xffin',$fin,$query);


        return $query;
    }





}
