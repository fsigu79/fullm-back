<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\SeriePac;
use App\Models\SqlModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\EmailController;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\Facades\Image;


class PacGuiasEntregaController extends Controller
{
    use FormatResponseTrait;




    private $sqlg = "select g.numero_guia_remision AS numero,
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
            (select fecfact31 from xbase.maefac where nofact31=g.numero_documento_origen) as fecha_factura,
            (select nopedido31 from xbase.maefac where nofact31=g.numero_documento_origen) as numero_pedido,
            (select distinct fecpedido30 from xbase.maeped30 where nopedido30=(select nopedido31 from xbase.maefac where nofact31=g.numero_documento_origen)) as fecha_pedido,
            g.usuario AS usuario
        from (xbase.guia_remision_electronica g join xbase.series_electronicas e on(((convert(substr(g.numero_guia_remision,1,7) using binary) = convert(e.serie using binary))
            and (e.tipodoc = '99'))))
        where g.fecha_emision>='xfinicio'  and g.fecha_emision<='xffin' and (g.estado_electronico = 2)";



    public function __construct()
    {
        //$this->middleware('auth:admin');
    }


    public function importarGuasPac(Request $request)
    {
        $input = $request->all();
        $inicio = $request['finicio'] . ' 00:00:00';
        $fin = $request['ffin'] . ' 23:59:00';
        $sql = '';

        // select de guias
        $sql = $this->generaQueryGuias('jcev', 'jcev', 'jcev', $inicio, $fin);
        $sql = $sql . ' UNION ALL ' . $this->generaQueryGuias('jcevcuenca2', 'jcevcuenca2', 'jcevcuenca2', $inicio, $fin);
        $sql = $sql . ' UNION ALL ' . $this->generaQueryGuias('jcevcuenca1', 'jcevcuenca1', 'jcevcuenca1', $inicio, $fin);
        $sql = $sql . ' UNION ALL ' . $this->generaQueryGuias('jcevgye1', 'jcevgye1', 'jcevgye1', $inicio, $fin);
        $sql = $sql . ' UNION ALL ' . $this->generaQueryGuias('jcevgye10', 'jcevgye10', 'jcevgye10', $inicio, $fin);
        $sql = $sql . ' UNION ALL ' . $this->generaQueryGuias('jcevuio1', 'jcevuio1', 'jcevuio1', $inicio, $fin);
        $sql = $sql . ' UNION ALL ' . $this->generaQueryGuias('jcevconsigvirt', 'jcevuio1', 'jcevuio1', $inicio, $fin);
        $sql = $sql . ' UNION ALL ' . $this->generaQueryGuias('jcevgyeassem', 'jcevgyeassem', 'jcevgyeassem', $inicio, $fin);
        $sql = $sql . ' UNION ALL ' . $this->generaQueryGuias('jcevconsigvirt', 'jcevconsigvirt', 'jcevgyeassem', $inicio, $fin);
        $sql = $sql . ' UNION ALL ' . $this->generaQueryGuias('jcevstecvir', 'jcevstecvir', 'jcevstecvir', $inicio, $fin);

        $list = DB::connection('mysqlpac')->select($sql);
        //return $this->getOk($list);
        //fsigu sqls
        /*$box = new SqlModel();
            $box->sql= $sql;
            $box->sql1=$sql;
            $box->save();*/

        foreach ($list as $detalle) {
            $results = DB::select('SELECT guias_pac_grabar(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
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
                $detalle->fecha_factura,
                $detalle->numero_pedido,
                $detalle->fecha_pedido,
                $detalle->usuario
            ]);
        };
        $termino = "IMPORTACION OK";
        return $this->getOk($termino);
    }



    public function generaQueryGuias($bodega, $bodega1, $bodega2, $inicio, $fin)
    {
        $query =  str_replace('xbase', $bodega, $this->sqlg);
        $query =  str_replace('xfinicio', $inicio, $query);
        $query =  str_replace('xffin', $fin, $query);
        return $query;
    }


    public function guiasList(Request $request)
    {
        $input = $request->all();
        $inicio = $request['finicio'] . ' 00:00:00';
        $fin = $request['ffin'] . ' 23:59:00';
        $transportista_id = $request['transportista_id'];
        $list = [];

        if ($transportista_id == 0) {
            $sql = "SELECT g.id, numero_guia_remision, fecha_emision, nombre_transportista, ruc_transportista,
            codigo_origen, direccion_origen, motivo_traslado, codigo_destino, direccion_destino,
            direccion, direccion_establecimiento, fecha_inicio_transporte, fecha_fin_transporte,
            codigo_cliente, g.ruc, nombre_cliente, telefono, observacion, numero_documento_origen,
            fecha_factura,numero_pedido,fecha_pedido,
            usuario, transportista_id, fecha_asignacion, esasignado, fecha_inicio_traslado_transportista,
            inicio_transporte, fecha_entrega_transportista, foto_entrega, foto_entrega1, esentregado,
            g.esactivo,t.nombres as razon_social,t.chofer
            FROM guiaspac g
                left join transportistas t on t.user_id=g.transportista_id
            WHERE fecha_emision>=? and fecha_emision<=?
            order by numero_guia_remision";
             $list = DB::select($sql,[$inicio,$fin]);

        }else{
            $sql="SELECT g.id, numero_guia_remision, fecha_emision, nombre_transportista, ruc_transportista,
            codigo_origen, direccion_origen, motivo_traslado, codigo_destino, direccion_destino,
            direccion, direccion_establecimiento, fecha_inicio_transporte, fecha_fin_transporte,
            codigo_cliente, g.ruc, nombre_cliente, telefono, observacion, numero_documento_origen,
            fecha_factura,numero_pedido,fecha_pedido,
            usuario, transportista_id, fecha_asignacion, esasignado, fecha_inicio_traslado_transportista,
            inicio_transporte, fecha_entrega_transportista, foto_entrega, foto_entrega1, esentregado,
            g.esactivo,t.nombres as razon_social,t.chofer
            FROM guiaspac g
                left join transportistas t on t.user_id=g.transportista_id
            WHERE fecha_emision>=? and fecha_emision<=? and transportista_id=?";
            $list = DB::select($sql, [$inicio, $fin, $transportista_id]);
        }



        return $this->getOk($list);
    }

    public function guiasListTransportista(Request $request)
    {
        $input = $request->all();
        $inicio = $request['finicio'] . ' 00:00:00';
        $fin = $request['ffin'] . ' 23:59:00';
        $idtran = $request['transportista_id'];

        $sql = "SELECT id, numero_guia_remision, fecha_emision, nombre_transportista, ruc_transportista,
                    codigo_origen, direccion_origen, motivo_traslado, codigo_destino, direccion_destino,
                    direccion, direccion_establecimiento, fecha_inicio_transporte, fecha_fin_transporte,
                    codigo_cliente, ruc, nombre_cliente, telefono, observacion, numero_documento_origen,
                    fecha_factura,numero_pedido,fecha_pedido,
                    usuario, transportista_id, fecha_asignacion, esasignado, fecha_inicio_traslado_transportista,
                    inicio_transporte, fecha_entrega_transportista, foto_entrega, foto_entrega1, esentregado,
                    esactivo, longitud, latitud
	        FROM guiaspac
            WHERE fecha_emision>=? and fecha_emision<=? and transportista_id=?
            ORDER BY fecha_emision desc";

        $list = DB::select($sql, [$inicio, $fin, $idtran]);

        return $this->getOk($list);
    }

    public function guiasListTransportistaPendientes(Request $request)
    {
        $input = $request->all();
        $inicio = $request['finicio'] . ' 00:00:00';
        $fin = $request['ffin'] . ' 23:59:00';
        $idtran = $request['transportista_id'];

        $sql = "SELECT id, numero_guia_remision, fecha_emision, nombre_transportista, ruc_transportista,
                    codigo_origen, direccion_origen, motivo_traslado, codigo_destino, direccion_destino,
                    direccion, direccion_establecimiento, fecha_inicio_transporte, fecha_fin_transporte,
                    codigo_cliente, ruc, nombre_cliente, telefono, observacion, numero_documento_origen,
                    fecha_factura,numero_pedido,fecha_pedido,
                    usuario, transportista_id, fecha_asignacion, esasignado, fecha_inicio_traslado_transportista,
                    inicio_transporte, fecha_entrega_transportista, foto_entrega, foto_entrega1, esentregado,
                    esactivo
	        FROM guiaspac
            WHERE fecha_emision>=? and fecha_emision<=? and transportista_id=? and esentregado=0
            ORDER BY fecha_emision asc";

        $list = DB::select($sql, [$inicio, $fin, $idtran]);

        return $this->getOk($list);
    }

    public function guiasListTransportistaFinalizado(Request $request)
    {
        $input = $request->all();
        $inicio = $request['finicio'] . ' 00:00:00';
        $fin = $request['ffin'] . ' 23:59:00';
        $idtran = $request['transportista_id'];

        $sql = "SELECT id, numero_guia_remision, fecha_emision, nombre_transportista, ruc_transportista,
                    codigo_origen, direccion_origen, motivo_traslado, codigo_destino, direccion_destino,
                    direccion, direccion_establecimiento, fecha_inicio_transporte, fecha_fin_transporte,
                    codigo_cliente, ruc, nombre_cliente, telefono, observacion, numero_documento_origen,
                    fecha_factura,numero_pedido,fecha_pedido,
                    usuario, transportista_id, fecha_asignacion, esasignado, fecha_inicio_traslado_transportista,
                    inicio_transporte, fecha_entrega_transportista, foto_entrega, foto_entrega1, esentregado,
                    esactivo
	        FROM guiaspac
            WHERE fecha_emision>=? and fecha_emision<=? and transportista_id=? and esentregado=1
            ORDER BY fecha_emision asc";

        $list = DB::select($sql, [$inicio, $fin, $idtran]);

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
            $guia = $input['num_guia'];
            $idtransportista = $input['transportista_id'];

            $sql = "update guiaspac set transportista_id=?,fecha_asignacion=current_timestamp,esasignado=1
                where numero_guia_remision=?";
            $order = DB::update($sql, [$idtransportista, $guia]);

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
            $guia = $input['num_guia'];
            $idtransportista = $input['transportista_id'];

            $sql = "update guiaspac set fecha_inicio_traslado_transportista=current_timestamp,inicio_transporte=1

                where numero_guia_remision=?";
            $order = DB::update($sql, [$guia]);


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

    public function actualizaFirma(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'guia_id' => 'required',
            ],
            [
                'guia_id.required' => 'El codigo del guia_id es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $idguia = $input['guia_id'];
            $foto_firma = $input['foto_firma'];

            $sql = "update guiaspac set foto_entrega1=?,esfirmado=1
                    where id=?";
            $order = DB::update($sql, [$foto_firma, $idguia]);


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
            $guia = $input['num_guia'];
            $idtransportista = $input['transportista_id'];
            $longitud = $input['longitud'];
            $latitud = $input['latitud'];
            $image = $input['image'];


            $sql = "update guiaspac set fecha_entrega_transportista=current_timestamp,esentregado=1,
                        longitud=?,latitud=?,foto_entrega=?
                where numero_guia_remision=?";
            $order = DB::update($sql, [$longitud, $latitud, $image, $guia]);


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

    public function getImage($filename)
    {
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

    public function addImage(Request $request)
    {
        try {
            //     $image=$request->file('image');

            //     if($image){
            //         $image_path=$image->getClientOriginalName();
            //        \Storage::disk('images')->put($image_path, \File::get($image));
            //     }
            //     $data=array(

            //        'image'=>$image,
            //        'status'=>'success'
            //    );
            //    return response()->json($data,200);

            $image = $request->file('image');

            if ($image) {
                // Obtén el nombre original del archivo
                $imageFileName = $image->getClientOriginalName();

                // Comprimir la imagen antes de guardarla
                $compressedImage = Image::make($image);
                $compressedImage->resize(800, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode('jpg', 80); // 80 es la calidad de compresión, puedes ajustarlo según tus necesidades

                // Guardar la imagen comprimida como JPG
                // eliminar la concatenacion con .jpg
                Storage::disk('images')->put($imageFileName, $compressedImage->stream());

                // Puedes obtener la URL de la imagen guardada
                //$imageUrl = Storage::disk('images')->url($imageFileName);

                $data = [
                    //'image_url' => $imageUrl,
                    'status' => 'success'
                ];

                return response()->json($data, 200);
            }

            $data = [
                'status' => 'error',
                'message' => 'No se proporcionó ninguna imagen.'
            ];

            return response()->json($data, 400);

        } catch (\Throwable $th) {
            $data = [
                'status' => 'error',
                'message' => $th->getMessage()
            ];
            return response()->json($data, 400);
        }
    }


    public function email_send_guias(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'fecha' => 'required',
            ],
            [
                'fecha.required' => 'La fecha es requerido.',
            ]
        );

        if (!$validation->fails()) {
            $input = $request->all();
            //send email verification
            try {
                //variable que contiene la plantilla en HTML

                $emailTemplate = "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta http-equiv='X-UA-Compatible' content='IE=edge'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Document</title>

    </head>

    <body>
        <div style='width: 100%;'>
            <div style='width:100%;background-color:#243A5F ;color:white;padding:1rem;font-family:arial;'>
                <h1 style='text-align:center;'>Ver detalles asumidos a la siguiente guia {Numguia}</h1>
            </div><br />
            <div style='font-family:Arial;font-size:medium;margin-top:2rem;'>
                <h3> Detallles de la guia: </h3>
                <label class='label' style='text-align:center;'>Fecha de emision: {fecha_emision}</label>
                <br><br>
                <label class='label' style='text-align:center;'>Fecha de asignacion: {fecha_asignacion}</label>
                <br><br>
                <label class='label' style='text-align:center;'>Fecha inicio transporte: {fecha_inicio}</label>
                <br><br>
                <label class='label' style='text-align:center; font-weight: bold;'>Fecha fin transporte: {fecha_fin}</label>
                <br><br>
                <label class='label style='text-align:center;'>Transportista: {transportista}</label>
                <br><br>
                <label class='label' style='text-align:center;'>Nombre Cliente: {codigo_cliente}</label>
                <br><br>
                <label class='label' style='text-align:center;'>RUC: {ruc}</label>
            </div>
            <div style='font-family:Arial;font-size:medium;margin-top:0.5rem;'>
                <p>Imagen de finalizacion:</p>
            </div>
            <div style='font-family:Arial;font-size:medium;margin:3rem; text-align: center;'>
                <img src='{imagen_url}' alt=''>
            </div>
            <div style='font-family:Arial;font-size:medium;margin-top:0.5rem;border-bottom:1px solid gray;'>
                <h4>Gracias por preferirnos.</h4>
            </div>
            <div style='font-family:Arial;font-size:medium;margin-top:1rem;padding-bottom:0.5rem;'>
            </div>
        </div>
    </body>
    </html>";

                $sql = 'SELECT * from guiaspac where numero_guia_remision =?';
                $request_db = DB::select($sql, [$input['numero_guia']]);

                $html = str_replace("{Numguia}", $input['numero_guia'], $emailTemplate);
                $html = str_replace("{fecha_fin}", $input['fecha'], $html);
                $html = str_replace("{imagen_url}", $input['image'], $html);

                $html = str_replace("{fecha_emision}", $request_db[0]->fecha_emision, $html);
                $html = str_replace("{fecha_asignacion}", $request_db[0]->fecha_asignacion, $html);
                $html = str_replace("{fecha_inicio}", $request_db[0]->fecha_inicio_transporte, $html);
                $html = str_replace("{transportista}", $request_db[0]->nombre_transportista, $html);
                $html = str_replace("{codigo_cliente}", $request_db[0]->codigo_cliente, $html);
                $html = str_replace("{ruc}", $request_db[0]->ruc, $html);

                $email = new EmailController();
                $email->sendEmail($html, $input['email'], "Guia Finalizada");
                //confirma en mensaje
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Correo enviado exitosamente',
                    'error' => false
                );

                return $data;
            } catch (\Throwable $th) {
                $data = array(
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'La error al enviar el correo',
                    'error message' => $th->getMessage()
                );
                return response()->json($data, $data['code']);
            }
        } else {
            return $this->updateErrCustom($validation->messages(), 'Datos inválidos');
        }
    }
}
