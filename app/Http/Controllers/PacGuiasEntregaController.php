<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\SeriePac;
use App\Models\SqlModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class PacGuiasEntregaController extends Controller
{
    use FormatResponseTrait;




private $sqlg="select g.numero_guia_remision AS numero,
            g.fecha_emision AS fecha,
            g.nombre_transportista,
            g.ruc_transportista AS ruc_transportista,
            g.bodega_origen AS codigo_origen,
            g.direccion_origen AS direccion_origen,
            g.motivo_traslado AS motivo_traslado,
            g.bodega_destino AS codigo_destino,
            g.direccion_destino AS direccion_destino,
            g.direccion_destino AS direccion,
            (select group_concat(jcev.maetab.nomtab separator ' ') from jcev.maetab where ((jcev.maetab.numtab = '01')
                    and (jcev.maetab.codtab in ('10','11'))) group by jcev.maetab.numtab) AS direccion_establecimiento,
            g.fecha_inicio_transporte AS fecha_inicio_transporte,
            g.fecha_fin_transporte AS fecha_fin_transporte,
            g.codigo_cliente AS codigo_cliente,
            g.codigo_cliente AS ruc,
            g.nombre_cliente AS nombre_cliente,
            g.telefono AS telefono,
            g.observacion AS observacion,
            g.numero_documento_origen AS numero_documento_origen,
            g.usuario AS usuario
        from (xbase.guia_remision_electronica g join xbase.series_electronicas e on(((convert(substr(g.numero_guia_remision,1,7) using binary) = convert(e.serie using binary))
            and (e.tipodoc = '99'))))
        where g.fecha_emision>='xfinicio'  and g.fecha_emision<='xffin' and (g.estado_electronico = 2)";



    public function __construct()
    {
        //$this->middleware('auth:admin');
    }


    public function Guiasgeneradas(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $sql='';

        // select de guias
        $sql=$this->generaQueryGuias('jcev','jcev','jcev',$inicio,$fin);
        // $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevcuenca2','jcevcuenca2','jcevcuenca2',$inicio,$fin);
        // $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevcuenca1','jcevcuenca1','jcevcuenca1',$inicio,$fin);
        // $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevgye1','jcevgye1','jcevgye1',$inicio,$fin);
        // $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevgye10','jcevgye10','jcevgye10',$inicio,$fin);
        // $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevuio1','jcevuio1','jcevuio1',$inicio,$fin);
        // $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevconsigvirt','jcevuio1','jcevuio1',$inicio,$fin);
        // $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevgyeassem','jcevgyeassem','jcevgyeassem',$inicio,$fin);
        // $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevconsigvirt','jcevconsigvirt','jcevgyeassem',$inicio,$fin);
        // $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevstecvir','jcevstecvir','jcevstecvir',$inicio,$fin);

        $list = DB::connection('mysqlpac')->select($sql);

        //fsigu sqls
        $box = new SqlModel();
            $box->sql= $sql;
            $box->sql1=$sql;
            $box->save();

        return $this->getOk($list);
    }

    public function generaQueryGuias($bodega,$bodega1,$bodega2,$inicio,$fin)
    {
        $query=  str_replace('xbase',$bodega,$this->sqlg);
        $query=  str_replace('xfinicio',$inicio,$query);
        $query=  str_replace('xffin',$fin,$query);
        return $query;
    }






}
