<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\SeriePac;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class PacCarteraController extends Controller
{
    use FormatResponseTrait;

    private $sqlgen="select nocte31 as rucfac,
                        nomcte31 as cliente,
                        nofact31 as documento,
                        date(fecfact31) as fecha,
                        (SELECT cascte01 FROM jcev.maecte WHERE jcev.maecte.codcte01=xbase.maefac.nocte31) as ruc,
                        (select nomtab from jcev.maetab where numtab='72' and codtab=trim(condpag31)) as condicion,
                        (SELECT nomtab FROM maetab,maecte WHERE numtab='79' AND codtab=trim(jcev.maecte.tipcte01) and jcev.maecte.codcte01=xbase.maefac.nocte31) as tipo,
                        (select max(totdoc43) from jcev.movcte where tipodoc43='02' and numdoc43=nofact31) as total,
                        (select sum(saldoregmov43) from jcev.movcte where tipodoc43='02' and numdoc43=nofact31) as saldo,
                        date((select max(feccobro43) from jcev.movcte where tipodoc43='02' and numdoc43=nofact31)) as fecha_cobro,
                        TIMESTAMPDIFF(DAY, date(fecfact31), date((select max(feccobro43) from jcev.movcte where tipodoc43='02' and numdoc43=nofact31)))  as plazo
                    from xbase.maefac
                    where fecfact31>='xfinicio' and fecfact31<='xffin'";

    public function __construct()
    {
        //$this->middleware('auth:admin');
    }


    public function seguroConfianza(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $sql='';


        try{
            $sql=$this->generaQuery('jcev',$inicio,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('jcevcuenca2',$inicio,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('jcevcuenca1',$inicio,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('jcevgye1',$inicio,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('jcevgye10',$inicio,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('jcevuio1',$inicio,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('jcevconsigvirt',$inicio,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('jcevgyeassem',$inicio,$fin);
            //$sql=$sql.' UNION ALL '.$this->generaQuery('jcevconsigvirt',$inicio,$fin);
            $sql=$sql.' UNION ALL '.$this->generaQuery('jcevstecvir',$inicio,$fin);

        $list = DB::connection('mysqlpac')->select($sql);

        return $this->getOk($list);

        }catch (\Exception $e) {
            return $this->insertErrCustom($input, $e->getMessage());
        }

    }


    public function generaQuery($bodega,$inicio,$fin)
    {
        $query=  str_replace('xbase',$bodega,$this->sqlgen);

        $query=  str_replace('xfinicio',$inicio,$query);
        $query=  str_replace('xffin',$fin,$query);


        return $query;
    }





}
