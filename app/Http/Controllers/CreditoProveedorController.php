<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\CreditoProveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class CreditoProveedorController extends Controller
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

        $sql=  "SELECT c.id, documento, numero, documento_proveedor,
                        proveedor_id,(nombres||' ' ||apellidos) as proveedor,
                        fecha,  total,c.esactivo
                FROM creditosp c
                inner join proveedores p on c.proveedor_id=p.id
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
                            $results=DB::select('SELECT credito_proveedor_elimina_detalle(?,?,?,?)',
                                [$input['id'],
                                $input['documento'],
                                $input['numero'],
                                $input['accion']
                                ]);
                        };

                        $results=DB::select('SELECT credito_provedor_grabar_cabecera(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                                    [$input['id'],
                                    $input['documento'],
                                    $input['numero'],
                                    $input['compra_id'],
                                    $input['documento_proveedor'],
                                    $input['autorizacion'],
                                    $input['fecha_caduca'],
                                    $input['proveedor_id'],
                                    $input['fecha'],
                                    $input['tipo_pago'],
                                    $input['nota_contable'],
                                    $input['banco_id'],
                                    $input['cheque'],
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
                                    $input['tipo'],
                                    $input['accion']
                                ]);



                        //obtenemos el numero de nota contable y numero actual de la factura
                        $valor_retorno =$results[0]->credito_provedor_grabar_cabecera;
                        $valor_retorno = trim($valor_retorno, '()');
                        $valor_array = explode (",", $valor_retorno);
                        $input['id']=$valor_array[0];
                        $input['numero']=$valor_array[1];
                        $input['nota_contable']=$valor_array[2];

                        //grabamos el detalle
                        foreach ($input['detalle'] as $detalle) {
                             $results=DB::select('SELECT credito_proveedor_grabar_detalle(?,?,?,?,?,?,?,?,?,?,?)',[
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
                        $results=DB::select('SELECT SELECT credito_proveedor_elimina_detalle(?,?,?,?)',
                        [$input['id'],
                        $input['documento'],
                        $input['numero'],
                        $input['accion']
                        ]);

                        $results=DB::select('SELECT SELECT credito_proveedor_elimina_cabecera(?,?,?,?)',
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
