<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\ValeCaja;
use App\Models\Retencion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Mail;
use PDF;


class ValeCajaController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function all()
    {
        $list = Order::orderBy('id', 'asc')->get();
        return $this->getOk($list);
    }



    public function list(Request $request)
    {
        $input = $request->all();
        $finicio=$request['finicio'];
        $ffin=$request['ffin'];
        $doc=$request['doc'];

        $sql=  "SELECT c.id, documento, numero, documento_proveedor,
                        proveedor_id,(nombres||' ' ||apellidos) as proveedor,
                        fecha, fecha_pago, total,
                        c.saldo,c.retencion, c.esactivo
                FROM compras c
                inner join proveedores p on c.proveedor_id=p.id
                where fecha>=? and fecha<=? and c.documento=?
                order by fecha desc,numero desc";
        $list = DB::select($sql,[$finicio,$ffin,$doc]);

        return $this->getOk($list);
    }

    public function findById($id)
    {
        $invoice = Servicio::with(['proveedor','retenciones','cuenta_gasto'])->find($id);
        return $this->getOk($invoice);
    }


    public function save(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'proveedor_id' => 'required',
            ],
            [
                'proveedor_id.required' => 'El proveedor es requerido.',
            ]
        );
        $ret=1;
        if (!$validation->fails()) {
                try{
                    $input = $request->all();
                    DB::beginTransaction();
                    if ($input['accion']!='Eliminar') {
                        //eliminamos el detalle si es modificacion
                        if ($input['accion']==='Modificar') {
                            $results=DB::select('SELECT compras_elimina_detalle(?,?,?,?)',
                                [$input['id'],
                                $input['documento'],
                                $input['numero'],
                                $input['accion']
                                ]);
                        };

                        $results=DB::select('SELECT compras_grabar_cabecera(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                                    [$input['id'],
                                    $input['documento'],
                                    $input['numero'],
                                    $input['documento_proveedor'],
                                    $input['proveedor_id'],
                                    $input['fecha'],
                                    $input['fecha_pago'],
                                    $input['observacion'],
                                    $input['nota_contable'],
                                    $input['tipo_pago'],
                                    $input['subtotal'],
                                    $input['subcero'],
                                    $input['subiva'],
                                    $input['iva'],
                                    $input['iva_porcentaje'],
                                    $input['descuento'],
                                    $input['total'],
                                    $input['ice'],
                                    $input['tretencion'],
                                    $input['saldo'],
                                    $input['metodopago_id'],
                                    $input['esactivo'],
                                    $input['orden_id'],
                                    $input['usuario_id'],
                                    $input['cuenta_gasto_id'],
                                    $input['ivaescreditotributario'],
                                    $input['accion']
                                ]);



                        //obtenemos el numero de nota contable y numero actual de la factura
                        $valor_retorno =$results[0]->compras_grabar_cabecera;
                        $valor_retorno = trim($valor_retorno, '()');
                        $valor_array = explode (",", $valor_retorno);
                        $input['id']=$valor_array[0];
                        $input['numero']=$valor_array[1];
                        $input['nota_contable']=$valor_array[2];

                         //grabamos la retencion
                        //$ret=$input['retencion'];
                        foreach ($input['retencion'] as $ret) {
                        $results=DB::select('SELECT retencion_grabar(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                                    [$ret['id'],
                                    $input['id'],
                                    $ret['documento'],
                                    $ret['numero'],
                                    $ret['tipo_compra'],
                                    $ret['tipo_documento'],
                                    $ret['autoriza_factura'],
                                    $ret['caduca_factura'],
                                    $ret['devuelveiva'],
                                    $ret['numero_retencion'],
                                    $ret['autorizacion_retencion'],
                                    $ret['serie'],
                                    $ret['iva_codigo'],
                                    $ret['iva_procentaje'],
                                    $ret['iva_base'],
                                    $ret['iva_valor'],
                                    $ret['iva_codigo1'],
                                    $ret['iva_procentaje1'],
                                    $ret['iva_base1'],
                                    $ret['iva_valor1'],
                                    $ret['fuente_codigo'],
                                    $ret['fuente_porcentaje'],
                                    $ret['fuente_base'],
                                    $ret['fuente_valor'],
                                    $ret['fuente_codigo1'],
                                    $ret['fuente_porcentaje1'],
                                    $ret['fuente_base1'],
                                    $ret['fuente_valor1'],
                                    $ret['total'],
                                    $ret['informacion_pago'],
                                    $ret['codigo_pais'],
                                    $ret['forma_pago'],
                                    $input['accion']
                                ]);
                            };

                    }else{

                        $results=DB::select('SELECT SELECT compras_elimina_cabecera(?,?,?,?)',
                        [$input['id'],
                        $input['documento'],
                        $input['numero'],
                        $input['accion']
                        ]);

                    }
                    //grabar nota contable
                    DB::commit();

                    return $this->insertOk($ret);

                } catch (\Exception $e) {
                    DB::rollBack();

                    return $this->insertErrCustom($ret, $e->getMessage());
                }

        } else {
            return $this->insertErrCustom($validation->messages(), 'Datos inválidos');
        }
    }
}
