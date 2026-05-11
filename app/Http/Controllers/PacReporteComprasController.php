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

class PacReporteComprasController extends Controller
{
    use FormatResponseTrait;

    private $sqlgen="SELECT tipodocto31 , nofact31 , nocte31 , nomcte31 , ruc31 , fecfact31 , vtabta31 , descto31 ,
                        (vtabta31 - totsiniva31) as subtot, ((vtabta31 - totsiniva31) * 0.12) as subiva, totsiniva31,
                        codcte43 , tipodoc43 , numdoc43 , fecdoc43 , factcompra43 , seriecompra43 , autocompra43
                    FROM xbase.maecom
                    inner join jcev.movpag on xbase.maecom.tipodocto31 = jcev.movpag.tipodoc43 and xbase.maecom.nofact31 = jcev.movpag.numdoc43
                    WHERE fecfact31>='xfinicio' and fecfact31<='xffin' ";



    public function __construct()
    {
        //$this->middleware('auth:admin');
    }


    public function comprasTotales(Request $request)
    {
        $input = $request->all();
        //$bodega = $request['bodega_id'];
        $bodega_filtro='';
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $sql='';
                // select de ventas
        $sql=$this->generaQuery('jcev',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQuery('jcevcuenca2',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQuery('jcevcuenca1',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQuery('jcevgye1',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQuery('jcevgye10',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQuery('jcevuio1',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQuery('jcevconsigvirt',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQuery('jcevgyeassem',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQuery('jcevstecvir',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQuery('jcevgye3',$inicio,$fin);

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


    public function generaQuery($bodega,$inicio,$fin)
    {
        $query=  str_replace('xbase',$bodega,$this->sqlgen);
        $query=  str_replace('xfinicio',$inicio,$query);
        $query=  str_replace('xffin',$fin,$query);
        return $query;
    }






}
