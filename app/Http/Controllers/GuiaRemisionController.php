<?php

namespace App\Http\Controllers;

use App\Events\InvoiceEvent;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Jobs\RequestSri;
use App\Jobs\SenderEmail;
use App\Mail\InvoiceEmail;
use App\Models\Company;
use App\Models\GuiaRemision;
use App\Models\GuiaRemisionDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use DOMDocument;
use PDF;
use Illuminate\Support\Str;
use App\Models\SqlModel;


class GuiaRemisionController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function list(Request $request)
    {

        $finicio = $request['finicio'];
        $ffin = $request['ffin'];
        $doc = $request['doc'];


        $sql = "SELECT gr.id, gr.ruc, gr.cliente, gr.status, gr.autorizacion, gr.status_code, gr.message_error, gr.aditional_message_error,
                gr.documento, gr.serie, gr.numero,(gr.serie||'-'||lpad(gr.numero,9,'0')) as guia_numero,
                gr.fecha_inicio, gr.fecha_fin, gr.transportista_id,
                COALESCE(gr.nombre_transportista, t.nombres) as transportista, gr.observacion
                FROM guias_remision gr
                LEFT JOIN transportistas t ON gr.transportista_id = t.id
                WHERE gr.fecha_inicio >= ? AND gr.fecha_inicio <= ? AND gr.documento = ?
                ORDER BY gr.numero DESC";

        $list = DB::select($sql, [$finicio, $ffin, $doc]);

        return $this->getOk($list);
    }

    public function findById($id)
    {
        $invoice = GuiaRemision::with(['detalle', 'transportista'])->find($id);

        if ($invoice && $invoice->nombre_transportista) {
            $invoice->transportista = (object)[
                'nombres' => $invoice->nombre_transportista,
                'ruc'     => $invoice->ruc_transportista,
                'placa'   => $invoice->placa,
            ];
        }

        return $this->getOk($invoice);
    }

    public function downloadXML($id)
    {
        $invoice = GuiaRemision::select("xml")->find($id);
        return $this->getOk($invoice->xml);
    }

    public function downloadPdf($id)
    {
        try {
            $company = Company::find(1);
            $invoice = GuiaRemision::with(['detalle', 'transportista'])->find($id);

            if (!$invoice) {
                return response()->json([
                    'status' => 'error',
                    'code' => 'error',
                    'message' => "Invoice not found",
                ], 404);
            }

            if ($invoice->nombre_transportista) {
                $invoice->transportista = (object)[
                    'nombres' => $invoice->nombre_transportista,
                    'ruc'     => $invoice->ruc_transportista,
                    'placa'   => $invoice->placa,
                ];
            }

            $data = [
                'invoice' => $invoice,
                'company' => $company
            ];

            $pdf = PDF::loadView('guides', $data);
            return response($pdf->output(), 200)->header('Content-Type', 'application/pdf');
            //return $this->getOk($data);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'code' => 'error',
                'message' => $th->getMessage(),
            ], 400);
        }
    }

    public function resendEmail($id)
    {
        try {
            $company = Company::find(1);
            $invoice = GuiaRemision::with(['detalle', 'transportista'])->find($id);

            if (!$invoice) {
                return response()->json([
                    'status' => 'error',
                    'code' => 'error',
                    'message' => "Invoice not found",
                ], 404);
            }

            if ($invoice->nombre_transportista) {
                $invoice->transportista = (object)[
                    'nombres' => $invoice->nombre_transportista,
                    'ruc'     => $invoice->ruc_transportista,
                    'placa'   => $invoice->placa,
                ];
            }

            $email = new InvoiceEmail($invoice->xml, $company, $invoice, 'guides');
            $email->build();

            //dispatch(new SenderEmail($invoice, 'guides'));

            // $data = [
            //     'id' => $invoice->id,
            //     'status' => $invoice->status,
            //     'status_code' => $invoice->status_code,
            //     'message_error' => $invoice->message_error,
            //     'aditional_message_error' => $invoice->aditional_message_error,
            //     'autorizacion' => $invoice->autorizacion,
            // ];

            // event(new InvoiceEvent($data));

            return response()->json([
                'status' => 'ok',
                'code' => 'ok',
                'message' => "Email sent success",
                'data' => $invoice,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'code' => 'error',
                'message' => $th->getMessage(),
            ], 400);
        }
    }

    public function save(Request $request)
    {
        $input = '';
        $validation = Validator::make(
            $request->all(),
            [
                'ruc' => 'required',
                'fecha_inicio' => 'required',
                'fecha_fin' => 'required',
            ],
            [
                'ruc.required' => 'El cliente es requerido.',
                'fecha_inicio.required' => 'Fecha inicio es requerido.',
                'fecha_fin.required' => 'Fecha fin es requerido.',
            ]
        );

        if (!$validation->fails()) {
            try {
                $input = $request->all();

                if (!empty($input['transportista']['user_id'])) {
                    $input['transportista_id']      = $input['transportista']['user_id'];
                    $input['nombre_transportista']  = $input['transportista']['nombres'] ?? $input['nombre_transportista'] ?? null;
                    $input['ruc_transportista']     = $input['transportista']['ruc']     ?? $input['ruc_transportista']    ?? null;
                }

                DB::beginTransaction();

                if ($input['accion'] != 'Eliminar') {

                    $guia = GuiaRemision::find($input['id']);

                    // =========================
                    // CREATE / UPDATE
                    // =========================
                    if (!$guia) {
                        $serie = $input['serie'];
                        $numero = DB::select(
                            "SELECT numero + 1 AS numero FROM documentos WHERE codigo = ? and serie=? AND modulo = 'Ventas'",
                            [$input['documento'], $serie]
                        );

                        $guia = new GuiaRemision($input);
                        $guia->numero = $numero[0]->numero;
                        $input['numero'] = $numero[0]->numero;
                        $guia->save();

                        DB::update(
                            "update documentos set numero = numero + 1 where codigo = ? and modulo = 'Ventas'",
                            [$input['documento']]
                        );
                    } else {
                        $guia->update($input);
                    }

                    // =========================
                    // DETALLE
                    // =========================
                    $guia->detalle()->delete();

                    foreach ($input['detalle'] as $detalle) {
                        $detalleObj = new GuiaRemisionDetalle($detalle);
                        $detalleObj->guiar_id = $guia->id;
                        $detalleObj->documento = $guia->documento;
                        $detalleObj->numero = $guia->numero;
                        $detalleObj->save();

                        // Validación de origen para actualizar catálogo
                        if ($input['origen'] != 'MANUAL') {
                            $guia_numero = $guia->serie . Str::padLeft($guia->numero, 9, '0');

                            DB::update(
                                'update catalogo_series set guia_remision_id=?, guia_remision_numero=? where chasis=? and serie=? and documento=?',
                                [
                                    $guia->id,
                                    $guia_numero,
                                    $detalleObj->chasis,
                                    $detalleObj->serie,
                                    $guia->documento,
                                ]
                            );
                        }
                    }

                    // =========================
                    // SRI LOGIC
                    // =========================
                    //$sri = new SriFunctionsController("06", $input);
                    //$xml = $sri->getXml();
                    //$key = $sri->getAccessKey();

                    //$guia->xml = $xml;
                    $guia->status = 'PENDIENTE';
                    //$guia->autorizacion = $key;
                    $guia->save();

                    DB::commit();

                    // Notificar al SRI
                    $guianew = GuiaRemision::with(['detalle', 'transportista'])->find($guia->id);
                    //$this->requestToSri($guianew);

                    // =========================
                    // PAC
                    // =========================
                    $numeroFormateado = $guianew->serie . '-' . str_pad($guianew->numero, 9, '0', STR_PAD_LEFT);

                    try {
                        DB::connection('pgsql_optimus')->select(
                            'SELECT guias_pac_grabar(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                            [
                                'Fullmotos',
                                $numeroFormateado,
                                $guianew->fecha_inicio,
                                $guianew->nombre_transportista ?? '',
                                $guianew->ruc_transportista ?? '',
                                null,
                                $guianew->partida,
                                $guianew->motivo,
                                null,
                                $guianew->direccion,
                                $guianew->direccion,
                                $guianew->direccion,
                                $guianew->fecha_inicio,
                                $guianew->fecha_fin,
                                null,
                                $guianew->ruc,
                                $guianew->cliente,
                                null,
                                $guianew->telefono,
                                $guianew->observacion,
                                $guianew->documentos,
                                null,
                                '',
                                null,
                                auth()->user()->name ?? 'system',
                                $input['transportista']['id'] ?? $input['transportista_id'] ?? null
                            ]
                        );
                        \Log::info('INSERT PAC OK: ' . $numeroFormateado);
                    } catch (\Exception $e) {
                        \Log::error('ERROR PAC GRABAR: ' . $e->getMessage());
                    }

                    // =========================
                    // ASIGNACIÓN TRANSPORTISTA
                    // =========================
                    try {
                        if (!empty($input['transportista']['id'])) {
                            $transportistaOptimusId = $input['transportista']['id'];
                            $userId                 = $input['transportista']['user_id'] ?? null;
                            $nombreTransportista    = $input['transportista']['nombres']  ?? $input['nombre_transportista'] ?? null;
                            $rucTransportista       = $input['transportista']['ruc']      ?? $input['ruc_transportista']    ?? null;
                        } else {
                            $userId              = $input['transportista_id'] ?? null;
                            $transOptimus        = $userId
                                ? DB::connection('pgsql_optimus')->table('transportistas')->where('user_id', $userId)->first()
                                : null;
                            $transportistaOptimusId = $transOptimus->id      ?? null;
                            $nombreTransportista    = $transOptimus->nombres  ?? $input['nombre_transportista'] ?? null;
                            $rucTransportista       = $transOptimus->ruc      ?? $input['ruc_transportista']    ?? null;
                        }

                        if ($transportistaOptimusId) {
                            $actualizado = DB::connection('pgsql_optimus')->update(
                                "update guiaspac
                                 set nombre_transportista = ?,
                                     ruc_transportista = ?,
                                     transportista_id = ?,
                                     transportista_id_guia = ?,
                                     fecha_asignacion = current_timestamp,
                                     esasignado = 1
                                 where numero_guia_remision = ? and empresa = 'Fullmotos'",
                                [
                                    $nombreTransportista,
                                    $rucTransportista,
                                    $userId,
                                    $transportistaOptimusId,
                                    $numeroFormateado
                                ]
                            );
                            \Log::info('UPDATE GUIASPAC filas: ' . $actualizado);
                        }
                    } catch (\Exception $e) {
                        \Log::error('ERROR PAC ASIGNACION: ' . $e->getMessage());
                    }

                    return $this->insertOk($guia);

                } else {
                    // Lógica para eliminar (si se requiere)
                }

            } catch (\Exception $e) {
                DB::rollBack();
                return $this->insertErrCustom($input, $e->getMessage());
            }

        } else {
            return $this->insertErrCustom($validation->getMessageBag(), 'Datos inválidos');
        }
    }

    private function requestToSri($guia)
    {
        try {
            // //peticion a sri
            // //return $guia;
            // $result = $sri->soapRecuestRc($guia->xml);

            // //return $result;

            // if (!$result) {
            //     //retornar error
            //     $guia->status = $result->estado;
            //     $guia->status_code = "500";
            //     $guia->message_error = "SRI FUERA DE LINEA";
            //     $guia->aditional_message_error = "Los servicios del SRI no estan disponibles por el momento.";
            //     $guia->save();
            //     throw new RuntimeException("Los servicios del SRI no estan disponibles por el momento.");
            // }

            // // return $result;

            // if ($result->estado == "DEVUELTA") {
            //     if (
            //         is_array($result->comprobantes->comprobante->mensajes->mensaje) &&
            //         isset($result->comprobantes->comprobante->mensajes->mensaje[0]->identificador) &&
            //         $result->comprobantes->comprobante->mensajes->mensaje[0]->identificador == "43"
            //     ) {
            //         $guia->status = "AUTORIZADO";
            //         $guia->status_code = "200";
            //         $guia->message_error = "";
            //         $guia->aditional_message_error = "";
            //         $guia->autorizado = 1;
            //         $guia->save();
            //         return true;
            //     } elseif (
            //         is_object($result->comprobantes->comprobante->mensajes->mensaje) &&
            //         isset($result->comprobantes->comprobante->mensajes->mensaje->identificador) &&
            //         $result->comprobantes->comprobante->mensajes->mensaje->identificador == "43"
            //     ) {
            //         $guia->status = "AUTORIZADO";
            //         $guia->status_code = "200";
            //         $guia->message_error = "";
            //         $guia->aditional_message_error = "";
            //         $guia->autorizado = 1;
            //         $guia->save();
            //         return true;
            //     }

            //     if (is_array($result->comprobantes->comprobante->mensajes->mensaje)) {
            //         $guia->status = $result->estado;
            //         $guia->status_code = $result->comprobantes->comprobante->mensajes->mensaje[0]->identificador;
            //         $guia->message_error = $result->comprobantes->comprobante->mensajes->mensaje[0]->mensaje;
            //         if (isset($result->comprobantes->comprobante->mensajes->mensaje[0]->informacionAdicional)) {
            //             $guia->aditional_message_error =  $result->comprobantes->comprobante->mensajes->mensaje[0]->informacionAdicional;
            //         }
            //         $guia->save();
            //         throw new RuntimeException($result->comprobantes->comprobante->mensajes->mensaje[0]->informacionAdicional);
            //     } else {
            //         $guia->status = $result->estado;
            //         $guia->status_code = $result->comprobantes->comprobante->mensajes->mensaje->identificador;
            //         $guia->message_error = $result->comprobantes->comprobante->mensajes->mensaje->mensaje;
            //         if ($result->comprobantes->comprobante->mensajes->mensaje->informacionAdicional) {
            //             $guia->aditional_message_error =  $result->comprobantes->comprobante->mensajes->mensaje->informacionAdicional;
            //         }
            //         $guia->save();
            //         throw new RuntimeException($result->comprobantes->comprobante->mensajes->mensaje->informacionAdicional);
            //     }
            // }

            // if ($result->estado == "RECIBIDA") {
            //     //sleep(2);
            //     $resultAc = $sri->soapRecuestAc($guia->autorizacion);

            //     if ($resultAc->autorizaciones->autorizacion->estado == "EN PROCESO") {
            //         $guia->status = $resultAc->autorizaciones->autorizacion->estado;
            //         $guia->status_code = "400";
            //         $guia->message_error = "Factura en proceso.";
            //         $guia->aditional_message_error = "Reenviar mas tarde.";
            //         $guia->save();
            //         throw new RuntimeException("Factura en proceso.");
            //     }

            //     if ($resultAc->autorizaciones->autorizacion->estado == "NO AUTORIZADO") {
            //         $guia->status = $resultAc->autorizaciones->autorizacion->estado;
            //         $guia->status_code =  $resultAc->autorizaciones->autorizacion->mensajes->mensaje->identificador;
            //         $guia->message_error = $resultAc->autorizaciones->autorizacion->mensajes->mensaje->mensaje;
            //         $guia->aditional_message_error = $resultAc->autorizaciones->autorizacion->mensajes->mensaje->informacionAdicional;
            //         $guia->save();
            //         throw new RuntimeException($resultAc->autorizaciones->autorizacion->mensajes->mensaje->mensaje);
            //     }

            //     if ($resultAc->autorizaciones->autorizacion->estado == "AUTORIZADO") {
            //         $guia->status = $resultAc->autorizaciones->autorizacion->estado;
            //         $guia->status_code = "200";
            //         $guia->message_error = "";
            //         $guia->aditional_message_error = "";
            //         $guia->autorizado = 1;
            //         $guia->xml = $resultAc->autorizaciones->autorizacion->comprobante; //$this->parseToXml($resultAc->autorizaciones->autorizacion);
            //         $guia->fecha_autorizacion = $resultAc->autorizaciones->autorizacion->fechaAutorizacion;
            //         $guia->save();

            //         //envio de email en hilo
            //         //$email = new InvoiceEmail($invoice->xml, $company, $invoice, 'guides');
            //         //$email->build();
            //         try {
            //             dispatch(new SenderEmail($guia, 'guides'));
            //         } catch (\Throwable $th) {
            //             //throw $th;
            //         }
            //         return $resultAc;
            //     }
            //     return $resultAc;
            // }
            //dispatch(new \App\Jobs\RequestSri($guia, 'guides'));
            RequestSri::dispatch($guia, 'guides');
            //return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    private function parseToXml($data)
    {
        // // Obtener la cadena de texto desde la propiedad del objeto si es necesario
        // if (is_object($data)) {
        //     // Asegúrate de ajustar 'propertyName' al nombre de la propiedad correcta
        //     $data = $data->propertyName;
        // }

        // // Decodificar las entidades HTML
        // $decodedData = html_entity_decode($data);

        // // Crear un nuevo documento DOM
        // $dom = new DOMDocument('1.0', 'UTF-8');
        // // Cargar el XML en el documento DOM
        // $dom->loadXML($decodedData);

        // // Obtener el elemento <autorizacion>
        // $autorizacion = $dom->getElementsByTagName('autorizacion')->item(0);

        // // Crear un nuevo documento DOM solo con el elemento <autorizacion>
        // $autorizacionDom = new DOMDocument('1.0', 'UTF-8');
        // $autorizacionNode = $autorizacionDom->importNode($autorizacion, true);
        // $autorizacionDom->appendChild($autorizacionNode);

        // // Obtener el XML como string
        // $xmlString = $autorizacionDom->saveXML();

        // // Guardar el XML en la variable $xml
        //$xml = htmlspecialchars($xmlString);

        //return $this->arrayToXml($data);;
    }

    // private function arrayToXml($array, $rootElement = null, $xml = null)
    // {
    //     $xml = $xml ?: new \SimpleXMLElement($rootElement ? '<' . $rootElement . '/>' : '<root/>');

    //     foreach ($array as $key => $value) {
    //         if (is_array($value)) {
    //             $this->arrayToXml($value, $key, $xml->addChild($key));
    //         } else {
    //             if (is_numeric($key)) {
    //                 $xml->addChild('item' . $key, $value);
    //             } else {
    //                 $xml->addChild($key, $value);
    //             }
    //         }
    //     }

    //     return $xml->asXML();
    // }

    public function resendToSri($id)
    {
        try {
            $guia = GuiaRemision::with(['detalle', 'transportista'])->find($id);

            if (!$guia) {
                return response()->json([
                    'status' => 'error',
                    'code' => 'error',
                    'message' => "Invoice not found",
                ], 404);
            }

            if ($guia->nombre_transportista) {
                $guia->transportista = (object)[
                    'nombres' => $guia->nombre_transportista,
                    'ruc'     => $guia->ruc_transportista,
                    'placa'   => $guia->placa,
                ];
            }

            $sri = new SriFunctionsController("06", $guia);
            $xml = $sri->getXml();
            $key = $sri->getAccessKey();
            $guia->xml = $xml;
            $guia->autorizacion = $key;

            //retornar error
            $guia->status = "ENVIANDO";
            $guia->status_code = null;
            $guia->message_error = null;
            $guia->aditional_message_error = null;
            $guia->save();
            //throw new RuntimeException("Los servicios del SRI no estan disponibles por el momento.");
            //$this->dispachEvent($guia);
            //$result = $this->requestToSri($sri, $guia);
            $this->requestToSri($guia);

            return response([
                'err' => false,
                'data' => $guia,
                'resultsri' => "ok",
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'code' => 'error',
                'message' => $th->getMessage(),
            ], 400);
        }
    }
}
