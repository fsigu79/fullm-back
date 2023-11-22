<?php

namespace App\Jobs;

use App\Events\InvoiceEvent;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SoapClient;
use stdClass;

class RequestSri implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $invoice;
    private $company;
    private $view;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($invoice, $view)
    {
        $this->invoice = $invoice;
        $this->view = $view;
        $this->company = Company::find(1);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if ($this->invoice->status == "AUTORIZADO") {
                $this->dispachEvent($this->invoice);
                return;
            }


            $result = $this->soapRecuestRc($this->invoice->xml);
            //event(new InvoiceEvent($result));

            if (!$result) {
                //retornar error
                $this->invoice->status = $result->estado;
                $this->invoice->status_code = "500";
                $this->invoice->message_error = "SRI FUERA DE LINEA";
                $this->invoice->aditional_message_error = "Los servicios del SRI no estan disponibles por el momento.";
                $this->invoice->save();
                //throw new RuntimeException("Los servicios del SRI no estan disponibles por el momento.");
                $this->dispachEvent($this->invoice);
            }

            // return $result;
            // if ($result->estado == "DEVUELTA") {
            //     $this->invoice->status = "AUTORIZADO";
            //     $this->invoice->status_code = "200";
            //     $this->invoice->message_error = "";
            //     $this->invoice->aditional_message_error = "";
            //     $this->invoice->autorizado = 1;
            //     $this->invoice->save();
            //     $this->dispachEvent($this->invoice);
            //     return;
            // }

            if ($result->estado == "DEVUELTA") {
                if (
                    is_array($result->comprobantes->comprobante->mensajes->mensaje) &&
                    isset($result->comprobantes->comprobante->mensajes->mensaje[0]->identificador) &&
                    $result->comprobantes->comprobante->mensajes->mensaje[0]->identificador == "43"
                ) {
                    $this->invoice->status = "AUTORIZADO";
                    $this->invoice->status_code = "200";
                    $this->invoice->message_error = "";
                    $this->invoice->aditional_message_error = "";
                    $this->invoice->autorizado = 1;
                    $this->invoice->save();
                    $this->dispachEvent($this->invoice);
                    return;
                } elseif (
                    is_object($result->comprobantes->comprobante->mensajes->mensaje) &&
                    isset($result->comprobantes->comprobante->mensajes->mensaje->identificador) &&
                    $result->comprobantes->comprobante->mensajes->mensaje->identificador == "43"
                ) {
                    $this->invoice->status = "AUTORIZADO";
                    $this->invoice->status_code = "200";
                    $this->invoice->message_error = "";
                    $this->invoice->aditional_message_error = "";
                    $this->invoice->autorizado = 1;
                    $this->invoice->save();
                    $this->dispachEvent($this->invoice);
                    return;
                }

                if (is_array($result->comprobantes->comprobante->mensajes->mensaje)) {
                    $this->invoice->status = $result->estado;
                    $this->invoice->status_code = $result->comprobantes->comprobante->mensajes->mensaje[0]->identificador;
                    $this->invoice->message_error = $result->comprobantes->comprobante->mensajes->mensaje[0]->mensaje;
                    if (isset($result->comprobantes->comprobante->mensajes->mensaje[0]->informacionAdicional)) {
                        $this->invoice->aditional_message_error =  $result->comprobantes->comprobante->mensajes->mensaje[0]->informacionAdicional;
                    }
                    $this->invoice->save();
                    //throw new RuntimeException($result->comprobantes->comprobante->mensajes->mensaje[0]->informacionAdicional);
                    $this->dispachEvent($this->invoice);
                    return;
                } else {
                    $this->invoice->status = $result->estado;
                    $this->invoice->status_code = $result->comprobantes->comprobante->mensajes->mensaje->identificador;
                    $this->invoice->message_error = $result->comprobantes->comprobante->mensajes->mensaje->mensaje;
                    if ($result->comprobantes->comprobante->mensajes->mensaje->informacionAdicional) {
                        $this->invoice->aditional_message_error =  $result->comprobantes->comprobante->mensajes->mensaje->informacionAdicional;
                    }
                    $this->invoice->save();
                    //throw new RuntimeException($result->comprobantes->comprobante->mensajes->mensaje->informacionAdicional);
                    $this->dispachEvent($this->invoice);
                    return;
                }
            }

            if ($result->estado == "RECIBIDA") {
                //sleep(2);
                $resultAc = $this->soapRecuestAc($this->invoice->autorizacion);
                //event(new InvoiceEvent($resultAc));
                if ($resultAc->autorizaciones->autorizacion->estado == "EN PROCESO") {
                    $this->invoice->status = $resultAc->autorizaciones->autorizacion->estado;
                    $this->invoice->status_code = "400";
                    $this->invoice->message_error = "Factura en proceso.";
                    $this->invoice->aditional_message_error = "Reenviar mas tarde.";
                    $this->invoice->save();
                    //throw new RuntimeException("Factura en proceso.");
                    $this->dispachEvent($this->invoice);
                    return;
                }

                if ($resultAc->autorizaciones->autorizacion->estado == "RECHAZADA") {
                    $this->invoice->status = $resultAc->autorizaciones->autorizacion->estado;
                    $this->invoice->status_code =  $resultAc->autorizaciones->autorizacion->mensajes->mensaje->identificador;
                    $this->invoice->message_error = $resultAc->autorizaciones->autorizacion->mensajes->mensaje->mensaje;
                    if(isset($resultAc->autorizaciones->autorizacion->mensajes->mensaje->informacionAdicional)){
                        $this->invoice->aditional_message_error = $resultAc->autorizaciones->autorizacion->mensajes->mensaje->informacionAdicional;
                    }
                    $this->invoice->save();
                    //throw new RuntimeException($resultAc->autorizaciones->autorizacion->mensajes->mensaje->mensaje);
                    $this->dispachEvent($this->invoice);
                    return;
                }

                if ($resultAc->autorizaciones->autorizacion->estado == "NO AUTORIZADO") {
                    $this->invoice->status = $resultAc->autorizaciones->autorizacion->estado;
                    $this->invoice->status_code =  $resultAc->autorizaciones->autorizacion->mensajes->mensaje->identificador;
                    $this->invoice->message_error = $resultAc->autorizaciones->autorizacion->mensajes->mensaje->mensaje;
                    $this->invoice->aditional_message_error = $resultAc->autorizaciones->autorizacion->mensajes->mensaje->informacionAdicional;
                    $this->invoice->save();
                    //throw new RuntimeException($resultAc->autorizaciones->autorizacion->mensajes->mensaje->mensaje);
                    $this->dispachEvent($this->invoice);
                    return;
                }

                if ($resultAc->autorizaciones->autorizacion->estado == "AUTORIZADO") {
                    $this->invoice->status = $resultAc->autorizaciones->autorizacion->estado;
                    $this->invoice->status_code = "200";
                    $this->invoice->message_error = "";
                    $this->invoice->aditional_message_error = "";
                    $this->invoice->autorizado = 1;
                    $this->invoice->xml = $resultAc->autorizaciones->autorizacion->comprobante; //$this->parseToXml($resultAc->autorizaciones->autorizacion);
                    $this->invoice->fecha_autorizacion = $resultAc->autorizaciones->autorizacion->fechaAutorizacion;
                    $this->invoice->save();
                    //envio de email en hilo
                    //$email = new InvoiceEmail($invoice->xml, $company, $invoice, 'guides');
                    //$email->build();
                    try {
                        dispatch(new SenderEmail($this->invoice, $this->view));
                    } catch (\Throwable $th) {
                        //throw $th;
                    }

                    $this->dispachEvent($this->invoice);
                    return;
                }


                $this->dispachEvent($this->invoice);
            }
        } catch (\Throwable $th) {
            $this->invoice->status = "ERROR";
            $this->invoice->status_code = 500;
            $this->invoice->message_error = "ERROR";
            $this->invoice->aditional_message_error = $th->getMessage();
            $this->invoice->save();
            $this->dispachEvent($this->invoice);
            throw $th;
        }
    }


    private function dispachEvent($invoice)
    {
        $data = [
            'id' => $invoice->id,
            'status' => $invoice->status,
            'status_code' => $invoice->status_code,
            'message_error' => $invoice->message_error,
            'aditional_message_error' => $invoice->aditional_message_error,
            'autorizacion' => $invoice->autorizacion,
        ];

        event(new InvoiceEvent($data));
    }

    private function soapRecuestRc($xml)
    {
        if ($this->company["environment"] == 1) {
            $wsdl = env('WS_SRI_RC');
            $wsdl='https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';
        } else {
            $wsdl = env('WS_SRI_RC_TEST');
            $wsdl='https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';
        }

        // Construir la solicitud SOAP
        $request = new stdClass();
        $request->xml = $xml;
        $soapClient = new SoapClient($wsdl, [
            'trace' => true, // Habilitar el seguimiento de la solicitud SOAP
            // Agrega cualquier configuración adicional requerida
            'connection_timeout' => 5000,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'keep_alive' => false,
        ]);
        // Enviar la solicitud SOAP
        $response = $soapClient->__soapCall('validarComprobante', [$request]);
        // Obtener el resultado de la respuesta
        $result = $response->RespuestaRecepcionComprobante;
        return $result;
    }

    private function soapRecuestAc($key)
    {
        if ($this->company["environment"] == 1) {
            $wsdl = env('WS_SRI_AC');
            $wsdl ='https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';
        } else {
            $wsdl = env('WS_SRI_AC_TEST');
            $wsdl ='https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';
        }
        // Construir la solicitud SOAP
        $request = new stdClass();
        $request->claveAccesoComprobante = $key;
        $soapClient = new SoapClient($wsdl, [
            'trace' => true, // Habilitar el seguimiento de la solicitud SOAP
            'connection_timeout' => 5000,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'keep_alive' => false,
        ]);
        // Enviar la solicitud SOAP
        $response = $soapClient->__soapCall('autorizacionComprobante', [$request]);
        // Obtener el resultado de la respuesta
        $result = $response->RespuestaAutorizacionComprobante;
        return $result;
    }
}
