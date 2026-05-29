<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SqlModel;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class FacturaPrepagoController extends Controller
{
    use FormatResponseTrait;

    private $sqlgen="select tipodocto31 as tdoc,
                                nofact31 as factura,
                                nocte31 as codcliente,
                                nomcte31 as cliente,
                                fecfact31 as fecfactura,
                                saldoregmov43 as saldo,
                                totdoc43 as total,
                                (saldoregmov43/totdoc43) as porcen_debe
                            from xbase.maefac f
                            inner join jcev.movcte m on  numdoc43=nofact31 and tipodoc43=tipodocto31
                            where f.prepago!=0 and tipodocto31='02'
                            and fecfact31>='xfinicio' and fecfact31<='xffin'";



    public function __construct()
    {
        //$this->middleware('auth:admin');
    }


    public function facturasPrepago(Request $request)
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
            $sql=$sql.' UNION ALL '.$this->generaQuery('jcevgye3',$inicio,$fin);

            $sqlfin="select tdoc,factura,codcliente,cliente,fecfactura,sum(saldo) as saldo,sum(total)as total,sum(saldo)/sum(total)as porcen_debe
                    from ( ".$sql." ) as t group by tdoc,factura,codcliente,cliente,fecfactura HAVING sum(saldo)>0";

        //fsigu sqls
        $box = new SqlModel();
        $box->sql = 'prepago';
        $box->sql1 = $sqlfin;
        $box->save();

        $list = DB::connection('mysqlpac')->select($sqlfin);

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
