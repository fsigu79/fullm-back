<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
                gr.fecha_inicio, gr.fecha_fin, gr.transportista_id, t.nombres as transportista, gr.observacion
                FROM guias_remision gr
                INNER JOIN transportistas t ON gr.transportista_id = t.id
                WHERE gr.fecha_inicio >= ? AND gr.fecha_fin <= ? AND gr.documento = ?
                ORDER BY gr.numero DESC";

        $list = DB::select($sql, [$finicio, $ffin, $doc]);

        return $this->getOk($list);
    }

    public function findById($id)
    {
        $invoice = GuiaRemision::with(['detalle', 'transportista'])->find($id);
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

            //$email = new InvoiceEmail($invoice->xml, $company, $invoice, 'guides');
            // $email->build();

            dispatch(new SenderEmail($invoice, 'guides'));

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
                DB::beginTransaction();
                if ($input['accion'] != 'Eliminar') {

                    $guia = GuiaRemision::find($input['id']);

                    if (!$guia) {
                        $numero = DB::select("SELECT numero + 1 AS numero FROM documentos WHERE codigo = ? AND modulo = 'Ventas'", [
                            $input['documento']
                        ]);


                        $guia = new GuiaRemision($input);
                        $guia->numero = $numero[0]->numero;
                        $input['numero'] = $numero[0]->numero;
                        $guia->save();

                        DB::update("update documentos set numero = numero + 1 where codigo = ? and modulo = 'Ventas'", [
                            $input['documento']
                        ]);
                    } else {
                        $guia->update($input);
                    }

                    $guia->detalle()->delete();
                    foreach ($input['detalle'] as $detalle) {
                        $detalleObj =  new GuiaRemisionDetalle($detalle);
                        $detalleObj->guiar_id = $guia->id;
                        $detalleObj->documento = $guia->documento;
                        $detalleObj->numero = $guia->numero;
                        $detalleObj->save();


                        if ($input['origen'] == 'PAC') {
                            $guia_numero = $guia->serie . Str::padLeft($guia->numero, 9, '0');;
                            $result = DB::update('update catalogo_series set guia_remision_id=?, guia_remision_numero=? where chasis=? and serie=?', [
                                $guia->id,
                                $guia_numero,
                                $detalleObj->chasis,
                                $detalleObj->serie,
                            ]);
                        }
                    };


                    $sri = new SriFunctionsController("06", $input);
                    $xml = $sri->getXml();
                    $key = $sri->getAccessKey();

                    $guia->xml = $xml;
                    $guia->autorizacion = $key;
                    $guia->save();

                    DB::commit();
                    //return $this->insertOk($guia);
                } else {
                    //Eliminar
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->insertErrCustom($input, $e->getMessage());
            }

            try {
                $result = $this->requestToSri($sri, $guia);
                //$result='nose';
                return response([
                    'err' => false,
                    'data' => $guia,
                    'result' => $result,
                ], 200);
            } catch (\Throwable $th) {
                return $this->insertErrCustom($input, $th->getMessage());
            }
        } else {
            return $this->insertErrCustom($validation->getMessageBag(), 'Datos inválidos');
        }
    }

    private function requestToSri($sri, $guia)
    {
        try {
            //peticion a sri
            //return $guia;
            $result = $sri->soapRecuestRc($guia->xml);

            //return $result;

            if (!$result) {
                //retornar error
                $guia->status = $result->estado;
                $guia->status_code = "500";
                $guia->message_error = "SRI FUERA DE LINEA";
                $guia->aditional_message_error = "Los servicios del SRI no estan disponibles por el momento.";
                $guia->save();
                throw new RuntimeException("Los servicios del SRI no estan disponibles por el momento.");
            }

            // return $result;

            if ($result->estado == "DEVUELTA") {
                if (
                    is_array($result->comprobantes->comprobante->mensajes->mensaje) &&
                    isset($result->comprobantes->comprobante->mensajes->mensaje[0]->identificador) &&
                    $result->comprobantes->comprobante->mensajes->mensaje[0]->identificador == "43"
                ) {
                    $guia->status = "AUTORIZADO";
                    $guia->status_code = "200";
                    $guia->message_error = "";
                    $guia->aditional_message_error = "";
                    $guia->autorizado = 1;
                    $guia->save();
                    return true;
                } elseif (
                    is_object($result->comprobantes->comprobante->mensajes->mensaje) &&
                    isset($result->comprobantes->comprobante->mensajes->mensaje->identificador) &&
                    $result->comprobantes->comprobante->mensajes->mensaje->identificador == "43"
                ) {
                    $guia->status = "AUTORIZADO";
                    $guia->status_code = "200";
                    $guia->message_error = "";
                    $guia->aditional_message_error = "";
                    $guia->autorizado = 1;
                    $guia->save();
                    return true;
                }



                if (is_array($result->comprobantes->comprobante->mensajes->mensaje)) {
                    $guia->status = $result->estado;
                    $guia->status_code = $result->comprobantes->comprobante->mensajes->mensaje[0]->identificador;
                    $guia->message_error = $result->comprobantes->comprobante->mensajes->mensaje[0]->mensaje;
                    if (isset($result->comprobantes->comprobante->mensajes->mensaje[0]->informacionAdicional)) {
                        $guia->aditional_message_error =  $result->comprobantes->comprobante->mensajes->mensaje[0]->informacionAdicional;
                    }
                    $guia->save();
                    throw new RuntimeException($result->comprobantes->comprobante->mensajes->mensaje[0]->informacionAdicional);
                } else {
                    $guia->status = $result->estado;
                    $guia->status_code = $result->comprobantes->comprobante->mensajes->mensaje->identificador;
                    $guia->message_error = $result->comprobantes->comprobante->mensajes->mensaje->mensaje;
                    if ($result->comprobantes->comprobante->mensajes->mensaje->informacionAdicional) {
                        $guia->aditional_message_error =  $result->comprobantes->comprobante->mensajes->mensaje->informacionAdicional;
                    }
                    $guia->save();
                    throw new RuntimeException($result->comprobantes->comprobante->mensajes->mensaje->informacionAdicional);
                }
            }

            if ($result->estado == "RECIBIDA") {
                //sleep(2);
                $resultAc = $sri->soapRecuestAc($guia->autorizacion);

                if ($resultAc->autorizaciones->autorizacion->estado == "EN PROCESO") {
                    $guia->status = $resultAc->autorizaciones->autorizacion->estado;
                    $guia->status_code = "400";
                    $guia->message_error = "Factura en proceso.";
                    $guia->aditional_message_error = "Reenviar mas tarde.";
                    $guia->save();
                    throw new RuntimeException("Factura en proceso.");
                }

                if ($resultAc->autorizaciones->autorizacion->estado == "NO AUTORIZADO") {
                    $guia->status = $resultAc->autorizaciones->autorizacion->estado;
                    $guia->status_code =  $resultAc->autorizaciones->autorizacion->mensajes->mensaje->identificador;
                    $guia->message_error = $resultAc->autorizaciones->autorizacion->mensajes->mensaje->mensaje;
                    $guia->aditional_message_error = $resultAc->autorizaciones->autorizacion->mensajes->mensaje->informacionAdicional;
                    $guia->save();
                    throw new RuntimeException($resultAc->autorizaciones->autorizacion->mensajes->mensaje->mensaje);
                }

                if ($resultAc->autorizaciones->autorizacion->estado == "AUTORIZADO") {
                    $guia->status = $resultAc->autorizaciones->autorizacion->estado;
                    $guia->status_code = "200";
                    $guia->message_error = "";
                    $guia->aditional_message_error = "";
                    $guia->autorizado = 1;
                    $guia->xml = $resultAc->autorizaciones->autorizacion->comprobante; //$this->parseToXml($resultAc->autorizaciones->autorizacion);
                    $guia->fecha_autorizacion = $resultAc->autorizaciones->autorizacion->fechaAutorizacion;
                    $guia->save();

                    //envio de email en hilo
                    //$email = new InvoiceEmail($invoice->xml, $company, $invoice, 'guides');
                    //$email->build();
                    try {
                        dispatch(new SenderEmail($guia, 'guides'));
                    } catch (\Throwable $th) {
                        //throw $th;
                    }

                    return $resultAc;
                }


                return $resultAc;
            }
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

            $sri = new SriFunctionsController("06", $guia);
            $xml = $sri->getXml();
            $key = $sri->getAccessKey();
            $guia->xml = $xml;
            $guia->autorizacion = $key;
            $guia->save();

            $result = $this->requestToSri($sri, $guia);

            return response([
                'err' => false,
                'data' => $guia,
                'resultsri' => $result,
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
