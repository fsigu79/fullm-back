<?php

namespace App\Http\Controllers;

use App\Http\Traits\FormatResponseTrait;
use App\Models\MovimientoInventario;
use App\Models\MovimientoInventarioDetalle;
use App\Models\Retencion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;


class MovimientoInventarioController extends Controller
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

        $sql=  "SELECT c.id, documento, numero, destino_id,nombre as destino,
                        fecha, total,c.esactivo
                FROM movimientos c
                inner join destinos d on c.destino_id=d.id
                where fecha>=? and fecha<=? and c.documento=?
                order by fecha desc,numero desc";
        $list = DB::select($sql,[$finicio,$ffin,$doc]);

        return $this->getOk($list);
    }

    public function findById($id)
    {
        $invoice = MovimientoInventario::with(['MovimientoDetalle','movimientoDetalle.product','destino'])->find($id);
        return $this->getOk($invoice);
    }


    public function save(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'destino_id' => 'required',
            ],
            [
                'destino_id.required' => 'El destino es requerido.',
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
                            $results=DB::select('SELECT movimiento_ingreso_elimina_detalle(?,?,?,?)',
                                [$input['id'],
                                $input['documento'],
                                $input['numero'],
                                'Modificar'
                                ]);
                        };

                        $results=DB::select('SELECT movimiento_ingreso_grabar_cabecera(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                                    [$input['id'],
                                    $input['documento'],
                                    $input['numero'],
                                    $input['fecha'],
                                    $input['nota_contable'],
                                    $input['precio_id'],
                                    $input['destino_id'],
                                    $input['referencia'],
                                    $input['observacion'],
                                    $input['subiva'],
                                    $input['subcero'],
                                    $input['subtotal'],
                                    $input['total'],
                                    $input['esactivo'],
                                    $input['accion']
                                ]);

                        //obtenemos el numero de nota contable y numero actual de la factura
                        $valor_retorno =$results[0]->movimiento_ingreso_grabar_cabecera;
                        $valor_retorno = trim($valor_retorno, '()');
                        $valor_array = explode (",", $valor_retorno);
                        $input['id']=$valor_array[0];
                        $input['numero']=$valor_array[1];
                        $input['nota_contable']=$valor_array[2];

                        //grabamos el detalle
                        foreach ($input['detalle'] as $detalle) {
                             $results=DB::select('SELECT movimiento_ingreso_grabar_detalle(?,?,?,?,?,?,?,?)',[
                            $input['id'],
                            $input['documento'],
                            $input['numero'],
                            $detalle['producto_id'],
                            $detalle['cantidad'],
                            $detalle['costo'],
                            $detalle['recibidos'],
                            $detalle['tieneiva'],
                            //$input['accion'],
                            ]);
                        };


                    }else{
                        $results=DB::select('SELECT SELECT movimiento_ingreso_elimina_detalle(?,?,?,?)',
                        [$input['id'],
                        $input['documento'],
                        $input['numero'],
                        $input['accion']
                        ]);

                        $results=DB::select('SELECT SELECT movimiento_ingreso_elimina_cabecera(?,?,?,?,?)',
                        [$input['id'],
                        $input['documento'],
                        $input['numero'],
                        $input['accion'],
                        $input['observacion']
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


    public function saveEgreso(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'destino_id' => 'required',
            ],
            [
                'destino_id.required' => 'El destino es requerido.',
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
                            $results=DB::select('SELECT movimiento_egreso_elimina_detalle(?,?,?,?)',
                                [$input['id'],
                                $input['documento'],
                                $input['numero'],
                                'Modificar'
                                ]);
                        };

                        $results=DB::select('SELECT movimiento_egreso_grabar_cabecera(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                                    [$input['id'],
                                    $input['documento'],
                                    $input['numero'],
                                    $input['fecha'],
                                    $input['nota_contable'],
                                    $input['precio_id'],
                                    $input['destino_id'],
                                    $input['referencia'],
                                    $input['observacion'],
                                    $input['subiva'],
                                    $input['subcero'],
                                    $input['subtotal'],
                                    $input['total'],
                                    $input['esactivo'],
                                    $input['accion']
                                ]);

                        //obtenemos el numero de nota contable y numero actual de la factura
                        $valor_retorno =$results[0]->movimiento_egreso_grabar_cabecera;
                        $valor_retorno = trim($valor_retorno, '()');
                        $valor_array = explode (",", $valor_retorno);
                        $input['id']=$valor_array[0];
                        $input['numero']=$valor_array[1];
                        $input['nota_contable']=$valor_array[2];

                        //grabamos el detalle
                        foreach ($input['detalle'] as $detalle) {
                             $results=DB::select('SELECT movimiento_egreso_grabar_detalle(?,?,?,?,?,?,?,?)',[
                            $input['id'],
                            $input['documento'],
                            $input['numero'],
                            $detalle['producto_id'],
                            $detalle['cantidad'],
                            $detalle['costo'],
                            $detalle['recibidos'],
                            $detalle['tieneiva'],
                            //$input['accion'],
                            ]);
                        };


                    }else{
                        $results=DB::select('SELECT SELECT movimiento_egreso_elimina_detalle(?,?,?,?)',
                        [$input['id'],
                        $input['documento'],
                        $input['numero'],
                        $input['accion']
                        ]);

                        $results=DB::select('SELECT SELECT movimiento_egreso_elimina_cabecera(?,?,?,?,?)',
                        [$input['id'],
                        $input['documento'],
                        $input['numero'],
                        $input['accion'],
                        $input['observacion']
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
