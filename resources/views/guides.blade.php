<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Guia de remision</title>
    <style>
        body {
            font-size: 9pt;
            font-family: Arial, Helvetica, sans-serif;
        }

        p {
            margin: 0.1em;
        }

        h1 {
            font-size: 11pt;
            font-weight: bold;
            margin: 0;
        }

        h2 {
            font-size: 10pt;
            font-weight: bold;
            margin: 0.2em;
        }

        .barcode svg {
            width: 100% !important;
        }
    </style>
</head>
<body>
    <div class="content diplay-print">
        <div class="invise-info" style="position: relative;">
            <div class="company-info" style="margin-right: 5px; width: 49.2%; float: left;">
                <figure style="width: 100%; text-align: center; margin: 0;">
                    @if ($company->logo !== null)
                     <!--<img src="{{Storage::disk('images')->url($company->logo)}}" alt="" style="max-width: 30em; max-height: 8.5em;">-->
                     <img src="http://190.95.225.90:8086/fullm-back//storage/app/images/logo-fullm.jpeg" alt="" style="max-width: 30em; max-height: 8.5em;">
                    @else
                        <h1 style="font-size: 3.5em; color: red; margin-bottom: 0.5em">Sin Logo</h1>
                    @endif
                </figure>
                <div class="company" style="border: 1px solid rgba(0, 0, 0, .5); padding:0.2em; ">
                    <h1 style="margin: 0; padding:0; text-transform: uppercase;">{{ $company->social_name }}</h1>
                    <p style="font-weight: bold;">Matriz: {{ $company->address }} </p>
                    <p>Telf: {{ $company->phone }}</p>
                    @if ($company->city !== null)
                        <p>{{ $company->city }}</p>
                    @endif
                    @if ($company->special_number !== null)
                        <p>Contribuyente Especial Nro.: {{ $company->special_number }}</p>
                    @endif
                    <p>Obligado a llevar contabilidad:
                        @if ($company->is_accounting == 1)
                            SI
                        @else
                            NO
                        @endif
                    </p>
                    <p>Agente de retención:
                        @if ($company->retention_agent == 1)
                            SI
                        @else
                            NO
                        @endif
                    </p>
                    @if ($company->retention_agent == 1)
                        <p>Agente de Retención Resolución No {{ $company->retention_number }}</p>
                    @endif
                </div>
            </div>
            <div class="fac-codes"
                style="border: 1px solid rgba(0, 0, 0, .5); padding:0.2em; width: 49%; margin: 0; float: right">
                <p style="font-weight: bold;">R.U.C.: <span>{{ $company->ruc }}</span></p>
                <p style="font-weight: bold;">GUIA DE REMISION</p>
                <p style="font-weight: bold;">N°: <span style="font-weight: normal; color: red;">
                    {{ $invoice->serie }}-{{ str_pad($invoice->numero, 9, '0', STR_PAD_LEFT) }}
                </span>
                </p>
                <p style="font-weight: bold;">Autorización SRI N°.</p>
                <p>{{ $invoice->autorizacion }}</p>
                <table>
                    <tbody>
                        <tr>
                            <td>Fecha de Autorización:</td>
                            @if (isset($invoice->fecha_autorizacion) && $invoice->fecha_autorizacion!='')
                                <td>{{ \Carbon\Carbon::parse($invoice->fecha_autorizacion)->format('d/m/Y H:i:s') }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td>Ambiente:</td>
                            <td>
                                @if ($company->environment == 0)
                                    PRUEBAS
                                @else
                                    PRODUCCION
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Emisión:</td>
                            <td>NORMAL</td>
                        </tr>

                    </tbody>
                </table>
                <p style="font-weight: bold;">CLAVE DE ACCESO</p>
                <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($invoice->autorizacion, 'C39')}}" alt="barcode"  height="40" style="width: 100%"/>

            </div>
        </div>
        <div style="clear:both;"></div>
        <table style="position: relative; width: 100%; border: 1px solid rgba(0, 0, 0, .5); margin-top: 5px;">
            <tbody style="padding:0.2em;">
                <tr>
                    <td style="padding:0.3em;">Razón Social/Nombres y Apellidos:</td>
                    <td style="padding:0.3em;">
                            {{ $invoice->transportista->nombres }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:0.3em;">Identificación Transportista:</td>
                    <td style="padding:0.3em;">{{ $invoice->transportista->ruc }}</td>

                    <td style="padding:0.3em;">Placa:</td>
                    <td style="padding:0.3em;">{{ $invoice->placa }}</td>
                </tr>
                <tr>
                    <td style="padding:0.3em;">Punto de Partida:</td>
                    <td style="padding:0.3em;">{{ $invoice->partida }}</td>
                </tr>
                <tr>
                    <td style="padding:0.3em;">Fecha Inicio Transporte:</td>
                    <td style="padding:0.3em;">{{ \Carbon\Carbon::parse($invoice->fecha_inicio)->format('d/m/Y') }}</td>

                    <td style="padding:0.3em;">Fecha Fin Transporte:</td>
                    <td style="padding:0.3em;">{{ \Carbon\Carbon::parse($invoice->fecha_fin)->format('d/m/Y') }}</td>
                </tr>

            </tbody>
        </table>

        <table style="position: relative; width: 100%; border: 1px solid rgba(0, 0, 0, .5); margin-top: 5px;">
            <tbody style="padding:0.2em;">
                <tr>
                    <td style="padding:0.3em;">Comprobante de Venta:</td>
                    <td style="padding:0.3em;"></td>

                    <td style="padding:0.3em;">Fecha de Emisión:</td>
                    <td style="padding:0.3em;">{{ \Carbon\Carbon::parse($invoice->fecha_inicio)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td style="padding:0.3em;">Número de Autorización:</td>
                    <td style="padding:0.3em;"></td>
                </tr>
                <tr>
                    <td style="padding:0.3em;">Motivo Traslado:</td>
                    <td style="padding:0.3em;">{{ $invoice->motivo }}</td>
                </tr>
                <tr>
                    <td style="padding:0.3em;">Destino (Punto de llegada):</td>
                    <td style="padding:0.3em;">{{ $invoice->direccion }}</td>
                </tr>
                <tr>
                    <td style="padding:0.3em;">Razón Social/Nombres y Apellidos:</td>
                    <td style="padding:0.3em;">
                        {{ $invoice->cliente }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:0.3em;">Identificación Destinatario:</td>
                    <td style="padding:0.3em;">{{ $invoice->ruc }}</td>

                    <td style="padding:0.3em;">Documento Aduanero:</td>
                    <td style="padding:0.3em;">
                       {{ $invoice->documento_aduanero }}
                    </td>
                </tr>
                <!-- <tr>
                    <td style="padding:0.3em;">Codigo Establecimiento Destino:</td>
                    <td style="padding:0.3em;"></td>
                </tr> -->
                <tr>
                    <td style="padding:0.3em;">Ruta:</td>
                    <td style="padding:0.3em;">{{ $invoice->ruta }}</td>
                </tr>

            </tbody>
        </table>


        <table style="width: 100%; border-collapse: collapse; border: 1px solid rgba(0, 0, 0, .5)">
            <thead style="background-color: rgba(0, 0, 0, .5); color: white;">
                <tr>
                    <th style="padding:0.2em; text-align: left;">Código</th>
                    <th style="padding:0.2em; text-align: left;">Descripción</th>
                    <th style="padding:0.2em; text-align: right;">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->detalle as $detalle)
                    <tr style="border: 0.5px solid rgba(0, 0, 0, .5); padding: 5px 0;">
                        <td style="padding: 0 1em;">{{ $detalle->codigo }}</td>
                        <td style="padding: 0 1em; max-width: 25em;">{{ $detalle->descripcion }}</td>
                        <td style="padding: 0 1em; text-align: right">{{ $detalle->cantidad }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="position: relative; margin-top: 5px;">
            <div style="margin-right: 5px; float: left; width: 100%;">
                <h2>Información Adicional</h2>
                <div style="border: 1px solid rgba(0, 0, 0, .5); padding:0.2em; ">
                    <table style="width:100%;">
                        <tbody>
                            @if (isset($invoice->direccion))
                                <tr>
                                    <td style="padding: 0.3em;">Direccion</td>
                                    <td style="padding: 0.3em;">{{ $invoice->direccion }}</td>
                                </tr>
                            @endif
                            @if (isset($invoice->email))
                                <tr>
                                    <td style="padding: 0.3em;">Email</td>
                                    <td style="padding: 0.3em;">{{ $invoice->email }}</td>
                                </tr>
                            @endif
                            @if (isset($invoice->observacion))
                                <tr>
                                    <td style="padding: 0.3em;">Observacion</td>
                                    <td style="padding: 0.3em;">{{ $invoice->observacion }}</td>
                                </tr>
                            @endif
                             @if (isset($invoice->documentos))
                                <tr>
                                    <td style="padding: 0.3em;">Factura</td>
                                    <td style="padding: 0.3em;">{{ $invoice->documentos }}</td>
                                </tr>
                            @endif

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- <div style="width: 100%; clear: both;">
            <p style="text-align: center; padding-top: 5px; font-weight: bold; font-size: 0.8em;">{{ company.text_voucher }}</p>
        </div> --}}
    </div>
</body>

</html>
