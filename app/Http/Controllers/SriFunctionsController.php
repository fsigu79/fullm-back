<?php

namespace App\Http\Controllers;

use App\Models\Company;
use DOMDocument;
use Exception;
use SoapClient;
use stdClass;
use App\Models\SqlModel;


class SriFunctionsController extends Controller
{

    private $code_doc;
    private $invoice;
    private $company;
    public function __construct($code_doc, $invoice)
    {
        $this->middleware('auth:admin');
        $this->code_doc = $code_doc;
        $this->invoice = $invoice;
        $this->company = $this->findCompany();
    }

    private function findCompany()
    {
        return Company::find(1);
    }


    public function getAccessKey() //cambiar a privado
    {
        $access_key = date('dmY', strtotime($this->invoice['fecha_inicio'])) .
            $this->code_doc .
            $this->company["ruc"] .
            ($this->company["environment"] == 0 ? '1' : '2') .
            explode('-', $this->invoice['serie'])[0] .
            explode('-', $this->invoice['serie'])[1] .
            str_pad((int)$this->invoice['numero'], 9, '0', STR_PAD_LEFT) .
            str_pad((int)$this->invoice['numero'], 8, '0', STR_PAD_LEFT) .
            '1';

        $access_key .= $this->module11($access_key);
        return $access_key;
    }

    private function module11($access)
    {
        $digits = array_map('intval', str_split($access));
        $digit = 11 - (array_reduce(
            array_map(
                function ($index, $data) {
                    return $data * (7 - ($index % 6));
                },
                array_keys($digits),
                $digits
            ),
            function ($acc, $data) {
                return $acc + $data;
            },
            0
        ) % 11
        );

        $digit = $digit == 11 ? 0 : $digit;
        $digit = $digit == 10 ? 1 : $digit;

        return $digit;
    }

    public function getXml(): string
    {
        try {
            switch ($this->code_doc) {
                case '06':
                    $xml = $this->getRemisionXML();
                    $xmlSign = $this->signXml($xml);
                    //$xmlSign =$xml;
                    return $xmlSign;
                default:
                    return "null";
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function signXml($xml): string
    {
        try {
            $basePath = base_path() . DIRECTORY_SEPARATOR . "signxml.py";
            $p12 = storage_path("app/public/" . $this->company["signature_file"]);
            $password = $this->company->decriptPassword($this->company["signature_password"]);
            $escapedXml = base64_encode($xml);
            $command = env('PYTHON')." $basePath --xml=$escapedXml --p12=$p12 --password=$password";
            //return $command;
            $output = shell_exec($command);

            $parts = explode("\n", $output);
            $firstWord = $parts[0];
            //return $firstWord;
            if ($firstWord == "None") {
                throw new Exception("Error executing Python file: " . $parts[1]);
            }
            /*if ($output === null) {
                throw new Exception("Error executing Python file ".$output);

            }*/
            if ($output === null) {
                throw new Exception("Error executing Python file ".$command);
            }
            return trim($output); // Devuelve la salida sin espacios en blanco adicionales
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function soapRecuestRc($xml)
    {
         //fsigu sqls
         $box = new SqlModel();
            $box->sql= 'soapRecuestRc';
            $box->sql1='wsdl';
            $box->save();


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

    public function soapRecuestAc($key)
    {
         //fsigu sqls
         $box = new SqlModel();
            $box->sql= 'soapRecuestAc';
            $box->sql1='2';
            $box->save();

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

    private function getRemisionXML()
    {
        $xml = new DOMDocument();
        $xml->formatOutput = true;

        $guiaRemision = $xml->createElement('guiaRemision');
        $guiaRemision->setAttribute("id", "comprobante");
        $guiaRemision->setAttribute("version", "1.1.0");
        $xml->appendChild($guiaRemision);

        $infoTributaria = $xml->createElement('infoTributaria');
        $guiaRemision->appendChild($infoTributaria);

        $ambiente = $xml->createElement("ambiente");
        $ambiente->appendChild($xml->createTextNode($this->company->environment == 0 ? "1" : "2"));
        $infoTributaria->appendChild($ambiente);

        $tipoEmision = $xml->createElement("tipoEmision");
        $tipoEmision->appendChild($xml->createTextNode("1"));
        $infoTributaria->appendChild($tipoEmision);

        $razonSocial = $xml->createElement("razonSocial");
        $razonSocial->appendChild($xml->createTextNode($this->company->social_name));
        $infoTributaria->appendChild($razonSocial);

        if (isset($this->company->name)) {
            $nombreComercial = $xml->createElement("nombreComercial");
            $nombreComercial->appendChild($xml->createTextNode($this->company->name));
            $infoTributaria->appendChild($nombreComercial);
        }

        $ruc = $xml->createElement("ruc");
        $ruc->appendChild($xml->createTextNode($this->company->ruc));
        $infoTributaria->appendChild($ruc);

        $claveAcceso = $xml->createElement("claveAcceso");
        $claveAcceso->appendChild($xml->createTextNode($this->getAccessKey()));
        $infoTributaria->appendChild($claveAcceso);

        $codDoc = $xml->createElement("codDoc");
        $codDoc->appendChild($xml->createTextNode($this->code_doc));
        $infoTributaria->appendChild($codDoc);

        $estab = $xml->createElement("estab");
        $estab->appendChild($xml->createTextNode(explode('-', $this->invoice['serie'])[0]));
        $infoTributaria->appendChild($estab);

        $ptoEmi = $xml->createElement("ptoEmi");
        $ptoEmi->appendChild($xml->createTextNode(explode('-', $this->invoice['serie'])[1]));
        $infoTributaria->appendChild($ptoEmi);

        $secuencial = $xml->createElement("secuencial");
        $secuencial->appendChild($xml->createTextNode(str_pad((int)$this->invoice['numero'], 9, '0', STR_PAD_LEFT)));
        $infoTributaria->appendChild($secuencial);

        $dirMatriz = $xml->createElement("dirMatriz");
        $dirMatriz->appendChild($xml->createTextNode($this->company->address));
        $infoTributaria->appendChild($dirMatriz);

        if ($this->company->retention_agent == 1) {
            $agenteRetencion = $xml->createElement("agenteRetencion");
            $agenteRetencion->appendChild($xml->createTextNode("1"));
            $infoTributaria->appendChild($agenteRetencion);
        }

        $infoGuiaRemision = $xml->createElement('infoGuiaRemision');
        $guiaRemision->appendChild($infoGuiaRemision);

        $dirEstablecimiento = $xml->createElement("dirEstablecimiento");
        $dirEstablecimiento->appendChild($xml->createTextNode($this->invoice["direccion"]));
        $infoGuiaRemision->appendChild($dirEstablecimiento);

        $dirPartida = $xml->createElement("dirPartida");
        $dirPartida->appendChild($xml->createTextNode($this->invoice['partida']));
        $infoGuiaRemision->appendChild($dirPartida);

        $razonSocialTransportista = $xml->createElement("razonSocialTransportista");
        $razonSocialTransportista->appendChild($xml->createTextNode($this->invoice['transportista']["nombres"]));
        $infoGuiaRemision->appendChild($razonSocialTransportista);

        $tipoIdentificacionTransportista = $xml->createElement("tipoIdentificacionTransportista");
        $tipoIdentificacionTransportista->appendChild($xml->createTextNode($this->invoice['transportista']["tiporuc"]));
        $infoGuiaRemision->appendChild($tipoIdentificacionTransportista);

        $rucTransportista = $xml->createElement("rucTransportista");
        $rucTransportista->appendChild($xml->createTextNode($this->invoice['transportista']["ruc"]));
        $infoGuiaRemision->appendChild($rucTransportista);

        if ($this->invoice['transportista']["esrise"] == 1) {
            $rise = $xml->createElement("rise");
            $rise->appendChild($xml->createTextNode("Contribuyente Regimen Simplificado RISE"));
            $infoGuiaRemision->appendChild($rise);
        }

        $obligadoContabilidad = $xml->createElement("obligadoContabilidad");
        $obligadoContabilidad->appendChild($xml->createTextNode($this->invoice['transportista']["llevaconta"] == 1 ? "SI" : "NO"));
        $infoGuiaRemision->appendChild($obligadoContabilidad);

        if (isset($this->invoice['transportista']["contibuyente_esp"])) {
            $espe=$this->invoice['transportista']["contibuyente_esp"];
            if ($espe!='0'){
                $contribuyenteEspecial = $xml->createElement("contribuyenteEspecial");
                $contribuyenteEspecial->appendChild($xml->createTextNode($this->invoice['transportista']["contibuyente_esp"]));
                $infoGuiaRemision->appendChild($contribuyenteEspecial);
            }
        }

        $fechaIniTransporte = $xml->createElement("fechaIniTransporte");
        $fechaIniTransporte->appendChild($xml->createTextNode(date('d/m/Y', strtotime($this->invoice['fecha_inicio']))));
        $infoGuiaRemision->appendChild($fechaIniTransporte);

        $fechaFinTransporte = $xml->createElement("fechaFinTransporte");
        $fechaFinTransporte->appendChild($xml->createTextNode(date('d/m/Y', strtotime($this->invoice['fecha_fin']))));
        $infoGuiaRemision->appendChild($fechaFinTransporte);

        $placa = $xml->createElement("placa");
        $placa->appendChild($xml->createTextNode($this->invoice["placa"]));
        $infoGuiaRemision->appendChild($placa);

        $destinatarios = $xml->createElement('destinatarios');
        $guiaRemision->appendChild($destinatarios);

        $destinatario = $xml->createElement('destinatario');
        $destinatarios->appendChild($destinatario);

        $identificacionDestinatario = $xml->createElement("identificacionDestinatario");
        $identificacionDestinatario->appendChild($xml->createTextNode($this->invoice["ruc"]));
        $destinatario->appendChild($identificacionDestinatario);

        $razonSocialDestinatario = $xml->createElement("razonSocialDestinatario");
        $razonSocialDestinatario->appendChild($xml->createTextNode($this->invoice["cliente"]));
        $destinatario->appendChild($razonSocialDestinatario);

        $dirDestinatario = $xml->createElement("dirDestinatario");
        $dirDestinatario->appendChild($xml->createTextNode($this->invoice["direccion"]));
        $destinatario->appendChild($dirDestinatario);

        if (isset($this->invoice['motivo'])) {
            $motivoTraslado = $xml->createElement("motivoTraslado");
            $motivoTraslado->appendChild($xml->createTextNode($this->invoice['motivo']));
            $destinatario->appendChild($motivoTraslado);
        }

        if (isset($this->invoice['documento_aduanero'])) {
            $docAduaneroUnico = $xml->createElement("docAduaneroUnico");
            $docAduaneroUnico->appendChild($xml->createTextNode($this->invoice['documento_aduanero']));
            $destinatario->appendChild($docAduaneroUnico);
        }

        $ruta = $xml->createElement("ruta");
        $ruta->appendChild($xml->createTextNode($this->invoice['ruta']));
        $destinatario->appendChild($ruta);

        $detalles = $xml->createElement('detalles');
        $destinatario->appendChild($detalles);

        foreach ($this->invoice['detalle'] as $detail) {
            $detalle = $xml->createElement('detalle');
            $detalles->appendChild($detalle);

            $codigoInterno = $xml->createElement("codigoInterno");
            $codigoInterno->appendChild($xml->createTextNode($detail["codigo"]));
            $detalle->appendChild($codigoInterno);

            $descripcion = $xml->createElement("descripcion");
            $descripcion->appendChild($xml->createTextNode($detail['descripcion']));
            $detalle->appendChild($descripcion);

            $cantidad = $xml->createElement("cantidad");
            $cantidad->appendChild($xml->createTextNode($detail['cantidad']));
            $detalle->appendChild($cantidad);
        }

        $infoAdicional = $xml->createElement('infoAdicional');
        $guiaRemision->appendChild($infoAdicional);

        if (isset($this->invoice["direccion"])) {
            $campoAdicional = $xml->createElement("campoAdicional");
            $campoAdicional->setAttribute("nombre", "Direccion");
            $campoAdicional->appendChild($xml->createTextNode($this->invoice["direccion"]));
            $infoAdicional->appendChild($campoAdicional);
        }

        if (isset($this->invoice["telefono"])) {
            $campoAdicional = $xml->createElement("campoAdicional");
            $campoAdicional->setAttribute("nombre", "Telefono");
            $campoAdicional->appendChild($xml->createTextNode($this->invoice["telefono"]));
            $infoAdicional->appendChild($campoAdicional);
        }

        if (isset($this->invoice["email"])) {
            $campoAdicional = $xml->createElement("campoAdicional");
            $campoAdicional->setAttribute("nombre", "Email");
            $campoAdicional->appendChild($xml->createTextNode($this->invoice["email"]));
            $infoAdicional->appendChild($campoAdicional);
        }

        if (isset($this->invoice['observacion'])) {
            $campoAdicional = $xml->createElement("campoAdicional");
            $campoAdicional->setAttribute("nombre", "Observacion");
            $campoAdicional->appendChild($xml->createTextNode($this->invoice['observacion']));
            $infoAdicional->appendChild($campoAdicional);
        }


        $xml_str = $xml->saveXML();
        return $xml_str;
    }
}
