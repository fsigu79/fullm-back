<?php

namespace App\Http\Controllers;

use App\Http\Traits\FormatResponseTrait;
use App\Http\Controllers\AuditoriaController;
use App\Models\MovimientoInventario;
use App\Models\MovimientoInventarioDetalle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use DateTime;

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



        $sql=  "SELECT c.id,c.serie, documento, c.numero as numero,cliente_id,destino_id,cliente_codigo,cliente_ruc,cliente_nombre,d.nombre as destino,
                        referencia,observacion,fecha, total,c.esactivo,aprobado,registrado,referencia_pac,negado,referencia_negado
                FROM movimientos c
                inner join destinos d on c.destino_id=d.id
                where fecha>=? and fecha<=? and c.documento=?
                order by fecha desc,numero desc";

        $list = DB::select($sql,[$finicio,$ffin,$doc]);

        return $this->getOk($list);
    }

    public function findById($id)
    {
        $invoice = MovimientoInventario::with(['MovimientoDetalle','destino'])->find($id);
        return $this->getOk($invoice);
    }

     public function detalleMovimientos(Request $request)
    {
        $input = $request->all();
        $finicio=$request['finicio'];
        $ffin=$request['ffin'];
        $doc=$request['doc'];



        $sql=  "SELECT c.id,c.serie, c.documento, c.numero as numero,(c.serie||''||LPAD(c.numero::text,9,'0'))as movimiento,destino_id,cliente_ruc,cliente_nombre,
                        d.nombre as destino,referencia,observacion,fecha,
                        CASE aprobado WHEN 1 THEN 'SI' ELSE 'NO' END as aprobado,
                        CASE registrado WHEN 1 THEN 'SI' ELSE 'NO' END as registrado,referencia_pac,
                        CASE negado WHEN 1 THEN 'SI' ELSE 'NO' END as negado,referencia_negado,
                        det.producto_codigo,det.producto_nombre as producto,det.cantidad
                FROM movimientos c
                inner join destinos d on c.destino_id=d.id
				inner join movimientosd det on c.id=det.movimiento_id
                where fecha>=? and fecha<=? and c.documento=?
                    and c.esactivo=1
                order by fecha desc,numero desc";

        $list = DB::select($sql,[$finicio,$ffin,$doc]);

        return $this->getOk($list);
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
                    $usuario=Auth::user();
                    DB::beginTransaction();
                    if ($input['accion']!='Eliminar') {
                        //eliminamos el detalle si es modificacion
                        if ($input['accion']==='Modificar') {
                            $results=DB::select('SELECT movimiento_pac_ingreso_elimina_detalle(?,?,?,?)',
                                [$input['id'],
                                $input['documento'],
                                $input['numero'],
                                'Modificar'
                                ]);
                        };

                        $results=DB::select('SELECT movimiento_ingreso_grabar_cabecera(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                                    [$input['id'],
                                    $input['documento'],
                                    $input['serie'],
                                    $input['numero'],
                                    $input['nota_contable'],
                                    $input['fecha'],
                                    $input['destino_id'],
                                    $input['cliente_id'],
                                    $input['cliente_nombre'],
                                    $input['cliente_codigo'],
                                    $input['cliente_ruc'],
                                    $input['precio_id'],
                                    $input['referencia'],
                                    $input['observacion'],
                                    $input['subtotal'],
                                    $input['subcero'],
                                    $input['subiva'],
                                    $input['total'],
                                    $input['esactivo'],
                                    $input['descripcion'],
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
                             $results=DB::select('SELECT movimiento_ingreso_grabar_detalle(?,?,?,?,?,?,?,?,?,?,?)',[
                            $input['id'],
                            $input['documento'],
                            $input['serie'],
                            $input['numero'],
                            $detalle['producto_id'],
                            $detalle['producto_codigo'],
                            $detalle['producto_nombre'],
                            $detalle['cantidad'],
                            $detalle['costo'],
                            $detalle['recibidos'],
                            $detalle['tieneiva'],
                            //$input['accion'],
                            ]);
                        };
                        $audi=new AuditoriaController();
                        $resAudi=$audi->Create('Inventarios', 'Ingresos','IN',
                                        $input['serie'],
                                        $input['numero'],
                                        'IN'.$input['serie'].str_pad($input['numero'], 9, "0", STR_PAD_LEFT),
                                        new DateTime(),
                                        $input['accion'],
                                        $input['total'] ,
                                        $input['fecha'],
                                        $usuario->id,
                                        $usuario->login);


                    }else{
                        $results=DB::select('SELECT movimiento_pac_ingreso_elimina_detalle(?,?,?,?)',
                        [$input['id'],
                        $input['documento'],
                        $input['numero'],
                        $input['accion']
                        ]);

                        $results=DB::select('SELECT movimiento_pac_ingreso_elimina_cabecera(?,?,?,?)',
                        [$input['id'],
                        $input['documento'],
                        $input['numero'],
                        $input['accion']
                        ]);
                        $audi=new AuditoriaController();
                        $resAudi=$audi->Create('Inventarios', 'Ingresos','IN',$input['serie'],$input['numero'], 'IN'.$input['serie'].'-'.str_pad($input['numero'], 9, "0", STR_PAD_LEFT),
                                                new DateTime(),
                                                $input['accion'],
                                                $input['total'] ,
                                                $input['fecha'],
                                                $usuario->id,
                                                $usuario->login);

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





    public function savePac(Request $request)
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

            $input = $request->all();
            $movimiento = new MovimientoInventario($input);
            $movimiento->save();

            foreach ($input['detalle'] as $parent_row) {
                $detalle = new MovimientoInventarioDetalle($parent_row);
                $detalle->movimiento_id = $movimiento->id;
                $detalle->save();
            }
            if ($movimiento) {
                return $this->insertOk($movimiento);
            } else {
                return $this->insertErr($movimiento);
            }
        } else {
            return $this->insertErrCustom($validation->messages(), 'Datos inválidos');
        }
    }

    public function updatePac(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'provider_id' => 'required',
            ],
            [
                'provider_id.required' => 'El nombre es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $movimiento = MovimientoInventario::find($input['id']);
            $movimiento->update($request->all());

            $detalles = $input['detalle'];
            $movimiento_id = $input['id'];
            $all_orders_ids = array_map(function ($detalle) use ($movimiento_id) {
                $new_order_box = MovimientoDetalle::updateOrCreate(
                    ['id' => $detalle['id']],
                    [
                        'movimiento_id' => $movimiento_id,
                        'box_id' => $detalle['box_id'],
                        'box_number' => $detalle['box_number'],
                    ],
                );
                //return $detalle['id'];
                return $new_order_box->id;
            }, $detalles);
            MovimientoInventarioDetalle::where('movimiento_id', $movimiento_id)->whereNotIn('id', $all_orders_ids)->delete();

            return $this->updateOk($all_orders_ids);

            if ($movimiento) {
                return $this->updateOk(null);
            } else {
                return $this->updateErr(null);
            }
        } else {
            return $this->updateErrCustom($validation->messages(), 'Datos inválidos');
        }
    }






    public function updateAprobar(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'id' => 'required',
            ],
            [
                'id.required' => 'El codigo es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $id=$input['id'];
            $apro=$input['aprobado'];

            $sql="update movimientos set aprobado=? where id=?";
            $order = DB::update($sql,[$apro,$id]);

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

    public function updateNegado(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'id' => 'required',
            ],
            [
                'id.required' => 'El codigo es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $id=$input['id'];
            $ref=$input['referencia_negado'];
            $regis=$input['negado'];

            $sql="update movimientos set negado=?,referencia_negado=? where id=?";
            $order = DB::update($sql,[$regis,$ref,$id]);

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


    public function updateRegistrado(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'id' => 'required',
            ],
            [
                'id.required' => 'El codigo es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $id=$input['id'];
            $ref=$input['referencia_pac'];
            $regis=$input['registrado'];

            $sql="update movimientos set registrado=?,referencia_pac=? where id=?";
            $order = DB::update($sql,[$regis,$ref,$id]);

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




}
