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


    public function leeGuiasPac(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $sql='';

        // select de guias
        $sql=$this->generaQueryGuias('jcev','jcev','jcev',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevcuenca2','jcevcuenca2','jcevcuenca2',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevcuenca1','jcevcuenca1','jcevcuenca1',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevgye1','jcevgye1','jcevgye1',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevgye10','jcevgye10','jcevgye10',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevuio1','jcevuio1','jcevuio1',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevconsigvirt','jcevuio1','jcevuio1',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevgyeassem','jcevgyeassem','jcevgyeassem',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevconsigvirt','jcevconsigvirt','jcevgyeassem',$inicio,$fin);
        $sql=$sql.' UNION ALL '.$this->generaQueryGuias('jcevstecvir','jcevstecvir','jcevstecvir',$inicio,$fin);

        $list = DB::connection('mysqlpac')->select($sql);
        //return $this->getOk($list);
        //fsigu sqls
        /*$box = new SqlModel();
            $box->sql= $sql;
            $box->sql1=$sql;
            $box->save();*/

        foreach ($list as $detalle) {
        $results=DB::select('SELECT guias_pac_grabar(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',[
                            $detalle->numero,
                            $detalle->fecha,
                            $detalle->nombre_transportista,
                            $detalle->ruc_transportista,
                            $detalle->codigo_origen,
                            $detalle->direccion_origen,
                            $detalle->motivo_traslado,
                            $detalle->codigo_destino,
                            $detalle->direccion_destino,
                            $detalle->direccion,
                            $detalle->direccion_establecimiento,
                            $detalle->fecha_inicio_transporte,
                            $detalle->fecha_fin_transporte,
                            $detalle->codigo_cliente,
                            $detalle->ruc,
                            $detalle->nombre_cliente,
                            $detalle->telefono,
                            $detalle->observacion,
                            $detalle->numero_documento_origen,
                            $detalle->usuario
                            ]);
        };
        $termino="IMPORTACION OK";
        return $this->getOk($termino);
    }



    public function generaQueryGuias($bodega,$bodega1,$bodega2,$inicio,$fin)
    {
        $query=  str_replace('xbase',$bodega,$this->sqlg);
        $query=  str_replace('xfinicio',$inicio,$query);
        $query=  str_replace('xffin',$fin,$query);
        return $query;
    }


    public function guiasList(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $sql="SELECT id, numero_guia_remision, fecha_emision, nombre_transportista, ruc_transportista,
                    codigo_origen, direccion_origen, motivo_traslado, codigo_destino, direccion_destino,
                    direccion, direccion_establecimiento, fecha_inicio_transporte, fecha_fin_transporte,
                    codigo_cliente, ruc, nombre_cliente, telefono, observacion, numero_documento_origen,
                    usuario, transportista_id, fecha_asignacion, esasignado, fecha_inicio_traslado_transportista,
                    inicio_transporte, fecha_entrega_transportista, foto_entrega, foto_entrega1, esentregado,
                    esactivo
	        FROM guiaspac
            WHERE fecha_emision>=? and fecha_emision<=?";

        $list = DB::select($sql,[$inicio,$fin]);

        return $this->getOk($list);
    }

    public function guiasListTransportista(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $idtran=$request['transportista_id'];

        $sql="SELECT id, numero_guia_remision, fecha_emision, nombre_transportista, ruc_transportista,
                    codigo_origen, direccion_origen, motivo_traslado, codigo_destino, direccion_destino,
                    direccion, direccion_establecimiento, fecha_inicio_transporte, fecha_fin_transporte,
                    codigo_cliente, ruc, nombre_cliente, telefono, observacion, numero_documento_origen,
                    usuario, transportista_id, fecha_asignacion, esasignado, fecha_inicio_traslado_transportista,
                    inicio_transporte, fecha_entrega_transportista, foto_entrega, foto_entrega1, esentregado,
                    esactivo
	        FROM guiaspac
            WHERE fecha_emision>=? and fecha_emision<=? and transportista_id=?
            ORDER BY fecha_emision desc";

        $list = DB::select($sql,[$inicio,$fin,$idtran]);

        return $this->getOk($list);
    }

    public function guiasListTransportistaPendientes(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $idtran=$request['transportista_id'];

        $sql="SELECT id, numero_guia_remision, fecha_emision, nombre_transportista, ruc_transportista,
                    codigo_origen, direccion_origen, motivo_traslado, codigo_destino, direccion_destino,
                    direccion, direccion_establecimiento, fecha_inicio_transporte, fecha_fin_transporte,
                    codigo_cliente, ruc, nombre_cliente, telefono, observacion, numero_documento_origen,
                    usuario, transportista_id, fecha_asignacion, esasignado, fecha_inicio_traslado_transportista,
                    inicio_transporte, fecha_entrega_transportista, foto_entrega, foto_entrega1, esentregado,
                    esactivo
	        FROM guiaspac
            WHERE fecha_emision>=? and fecha_emision<=? and transportista_id=? and esentregado=0
            ORDER BY fecha_emision asc";

        $list = DB::select($sql,[$inicio,$fin,$idtran]);

        return $this->getOk($list);
    }


    public function asignaTransportistas(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'transportista_id' => 'required',
            ],
            [
                'transportista_id.required' => 'El codigo del transportista es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $guia=$input['num_guia'];
            $idtransportista=$input['transportista_id'];

            $sql="update guiaspac set transportista_id=?,fecha_asignacion=current_timestamp,esasignado=1
                where numero_guia_remision=?";
            $order = DB::update($sql,[$idtransportista,$guia]);

            return $this->updateOk($input);

            if ($movimiento) {
                return $this->updateOk(null);
            } else {
                return $this->updateErr(null);
            }
        } else {
            return $this->updateErrCustom($validation->messages(), 'Datos inválidos');
        }
    }

    public function inicioTransporte(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'transportista_id' => 'required',
            ],
            [
                'transportista_id.required' => 'El codigo del transportista es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $guia=$input['num_guia'];
            $idtransportista=$input['transportista_id'];

            $sql="update guiaspac set fecha_inicio_traslado_transportista=current_timestamp,inicio_transporte=1
                where numero_guia_remision=?";
            $order = DB::update($sql,[$idtransportista,$guia]);

            return $this->updateOk($input);

            if ($movimiento) {
                return $this->updateOk(null);
            } else {
                return $this->updateErr(null);
            }
        } else {
            return $this->updateErrCustom($validation->messages(), 'Datos inválidos');
        }
    }

    public function finTransporte(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'transportista_id' => 'required',
            ],
            [
                'transportista_id.required' => 'El codigo del transportista es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $guia=$input['num_guia'];
            $idtransportista=$input['transportista_id'];

            $sql="update guiaspac set fecha_entrega_transportista=current_timestamp,esentregado=1
                where numero_guia_remision=?";
            $order = DB::update($sql,[$idtransportista,$guia]);

            return $this->updateOk($input);

            if ($movimiento) {
                return $this->updateOk(null);
            } else {
                return $this->updateErr(null);
            }
        } else {
            return $this->updateErrCustom($validation->messages(), 'Datos inválidos');
        }
    }

    public function getImage($filename){
        $isset = \Storage::disk('images')->exists($filename);
        //echo($filename);
        //echo($isset);
        if ($isset) {
            $file = \Storage::disk('images')->get($filename);
            return Response($file, 200);
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe',
            );
            return response()->json($data, $data['code']);
        }
    }

    public function addImage(Request $request){

        $image=$request->file('image');

        if($image){
            $image_path=$image->getClientOriginalName();
           \Storage::disk('images')->put($image_path, \File::get($image));
        }
        $data=array(

           'image'=>$image,
           'status'=>'success'
       );
       return response()->json($data,200);

    }





}
