<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Venta;
use App\Models\ventaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;


class CreditoClienteController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function list(Request $request)
    {
        $input = $request->all();
        $finicio=$request['finicio'];
        $ffin=$request['ffin'];
        $doc=$request['doc'];

        $sql=  "SELECT c.id, documento, numero, documento_cliente,
                        cliente_id,(nombre||' ' ||apellido) as cliente,
                        fecha,  total,c.esactivo
                FROM creditosc c
                inner join clientes p on c.cliente_id=p.id
                where fecha>=? and fecha<=? and c.documento=?
                order by fecha desc,numero desc";
        $list = DB::select($sql,[$finicio,$ffin,$doc]);

        return $this->getOk($list);
    }

    public function findById($id)
    {
        $invoice = CreditoProveedor::with(['CompraDetalle','compraDetalle.product','proveedor','retenciones'])->find($id);
        return $this->getOk($invoice);
    }


    public function save(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'cliente_id' => 'required',
            ],
            [
                'cliente_id.required' => 'El cliente es requerido.',
            ]
        );
        if (!$validation->fails()) {
                try{
                    $input = $request->all();
                    DB::beginTransaction();
                    if ($input['accion']!='Eliminar') {
                        //eliminamos el detalle si es modificacion
                        if ($input['accion']==='Modificar') {
                            $results=DB::select('SELECT credito_cliente_elimina_detalle(?,?,?,?)',
                                [$input['id'],
                                $input['documento'],
                                $input['numero'],
                                $input['accion']
                                ]);
                        };

                        $results=DB::select('SELECT credito_cliente_grabar_cabecera(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                                    [$input['id'],
                                    $input['documento'],
                                    $input['numero'],
                                    $input['venta_id'],
                                    $input['documento_cliente'],
                                    $input['cliente_id'],
                                    $input['autorizacion'],
                                    $input['fecha'],
                                    $input['fecha_caduca'],
                                    $input['tipo_credito'],
                                    $input['nota_contable'],
                                    $input['descripcion'],
                                    $input['subtotal'],
                                    $input['subcero'],
                                    $input['subiva'],
                                    $input['iva'],
                                    $input['iva_porcentaje'],
                                    $input['descuento'],
                                    $input['descuentop'],
                                    $input['total'],
                                    $input['saldo'],
                                    $input['esactivo'],
                                    $input['accion']
                                ]);



                        //obtenemos el numero de nota contable y numero actual de la factura
                        $valor_retorno =$results[0]->credito_cliente_grabar_cabecera;
                        $valor_retorno = trim($valor_retorno, '()');
                        $valor_array = explode (",", $valor_retorno);
                        $input['id']=$valor_array[0];
                        $input['numero']=$valor_array[1];
                        $input['nota_contable']=$valor_array[2];

                        //grabamos el detalle
                        foreach ($input['detalle'] as $detalle) {
                             $results=DB::select('SELECT credito_cliente_grabar_detalle(?,?,?,?,?,?,?,?,?,?,?)',[
                            $input['id'],
                            $input['documento'],
                            $input['numero'],
                            $detalle['producto_id'],
                            $detalle['descripcion'],
                            $detalle['cantidad'],
                            $detalle['descuento'],
                            $detalle['descuentop'],
                            $detalle['costo'],
                            $detalle['tieneiva'],
                            $input['accion'],
                            ]);
                        };



                    }else{
                        $results=DB::select('SELECT SELECT credito_cliente_elimina_detalle(?,?,?,?)',
                        [$input['id'],
                        $input['documento'],
                        $input['numero'],
                        $input['accion']
                        ]);

                        $results=DB::select('SELECT SELECT credito_cliente_elimina_cabecera(?,?,?,?)',
                        [$input['id'],
                        $input['documento'],
                        $input['numero'],
                        $input['accion']
                        ]);

                    }
                    //grabar nota contable
                    DB::commit();

                    return $this->insertOk($input);

                } catch (\Exception $e) {
                    DB::rollBack();

                    return $this->insertErrCustom($input, $e->getMessage());
                }

        } else {
            return $this->insertErrCustom($validation->messages(), 'Datos inválidos');
        }
    }

}
