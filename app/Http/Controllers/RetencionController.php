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

class RetencionController extends Controller
{
    use FormatResponseTrait;

    private $sqlgen="Select tipodoc43 as tipodocumento,M1.nomtab as documento,numdoc43 as numero_retencion,fecdoc43 as fecha,numvencob43,totdoc43 as total_retenciones,
                        ocurren43,nomcte01 as cliente,catcte01 cod_categoria,feccobro43, fedoc43,
                        codpagounif43, numrecibo43, valormov43, valorabono43 as valor_retencion, tipodocdb43, numdocdb43 as factura, ocurrecdocdb43, saldoregmov43, detalle43,
                        movcte.UID as usuario,efectcheque43,if(efectcheque43=6,'RETENCION IR','RETENCION IVA') as tipo_retencion,numdocpago43,obsdocpago43,codcte43 codigo_cliente,conta43
                    from xbase.movcte, xbase.maecte, xbase.maetab M1
                    WHERE M1.codtab=tipodoc43 and  M1.numtab='71'
                        and tipodoc43='52'
                        and fecdoc43>='xfinicio' and fecdoc43<='xffin'
                        and codcte43=codcte01
                    UNION ALL
                    Select tipodoc43,M1.nomtab as tipodoc,numdoc43,fecdoc43,numvencob43, totdoc43, ocurren43, nomcte01, catcte01, feccobro43,
                        fedoc43,codpagounif43,numrecibo43,valormov43,valorabono43,tipodocdb43,numdocdb43,ocurrecdocdb43,saldoregmov43,detalle43,
                        movcte2.UID as usuario,efectcheque43,if(efectcheque43=6,'RETENCION IR','RETENCION IVA') as tipo_retencion,numdocpago43,obsdocpago43,codcte43,conta43
                    from xbase.movcte2, xbase.maecte, xbase.maetab M1
                    WHERE M1.codtab=tipodoc43 and M1.numtab='71'
                        and tipodoc43='52'
                        and fecdoc43>='xfinicio' and fecdoc43<='xffin'
                        and codcte43=codcte01
                    order by tipodocumento,numero_retencion,ocurren43 ASC";





    public function __construct()
    {
        //$this->middleware('auth:admin');
    }


    public function retencionesClientes(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $sql='';
        $sqlnc='';



        $sql=  str_replace('xbase','jcev',$this->sqlgen);
        $sql=  str_replace('xfinicio',$inicio,$sql);
        $sql=  str_replace('xffin',$fin,$sql);

        //return $this->getOk($sql);
         //fsigu sqls
         /*$box = new SqlModel();
            $box->sql= $sql;
            $box->sql1=$sql;
            $box->save();*/

        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($sql);

        return $this->getOk($list);
    }




}
