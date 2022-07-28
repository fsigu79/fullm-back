<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\GuiaRemision;
use App\Models\GuiaRemisionDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;


class GuiaRemisionController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function list(Request $request)
    {
        $input = $request->all();
        $finicio = $request['finicio'];
        $ffin = $request['ffin'];
        $doc = $request['doc'];
        $cli_id = $request['cli_id'];

        $sql =  "SELECT gr.id,gr.documento,gr.serie,gr.numero,gr.cliente_id,c.ruc,c.apellido||' '||c.nombre as nombre,
                        gr.fecha_inicio,gr.fecha_fin,gr.transportista_id,t.nombres as transportista ,gr.observacion
                        FROM guias_remision gr
                        INNER JOIN clientes c on gr.cliente_id=c.id
                        INNER JOIN transportistas t on gr.transportista_id=t.id
                        WHERE gr.fecha_inicio >= ? and gr.fecha_fin<=?  and gr.documento=? and gr.cliente_id = coalesce(?, gr.cliente_id)";

        $list = DB::select($sql, [$finicio, $ffin, $doc,$cli_id]);

        return $this->getOk($list);
    }

    public function findById($id)
    {
        $invoice = GuiaRemision::with(['guiaDetalle','guiaDetalle.product','customer','transportista'])->find($id);
        return $this->getOk($invoice);
    }

    public function save(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'cliente_id' => 'required',
                'fecha_inicio' => 'required',
                'fecha_fin' => 'required',
            ],
            [
                'cliente_id.required' => 'El cliente es requerido.',
                'fecha_inicio.required' => 'Fecha inicio es requerido.',
                'fecha_fin.required' => 'Fecha fin es requerido.',
            ]
        );
        if (!$validation->fails()) {
            try {
                $input = $request->all();
                DB::beginTransaction();
                if ($input['accion'] != 'Eliminar') {
                    //eliminamos el detalle de la guia de remisión si es modificacion
                    if ($input['accion'] === 'Modificar') {
                        $results = DB::select(
                            'SELECT guias_remision_elimina_detalle(?,?)',
                            [
                                $input['id'],
                                $input['accion']
                            ]
                        );
                    };


                    $results = DB::select(
                        'SELECT guias_remision_grabar_cabecera(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                        [
                            $input['id'],
                            $input['documento'],
                            $input['serie'],
                            $input['numero'],
                            $input['factura_id'],
                            $input['factura_cliente'],
                            $input['cliente_id'],
                            $input['partida'],
                            $input['fecha_inicio'],
                            $input['fecha_fin'],
                            $input['direccion'],
                            $input['ruta'],
                            $input['motivo'],
                            $input['documento_aduanero'],
                            $input['placa'],
                            $input['transportista_id'],
                            $input['observacion'],
                            $input['autorizacion'],
                            $input['esactivo'],
                            $input['usuario_id'],
                            $input['accion'],
                        ]
                    );
                    //obtenemos el numero de nota contable y numero actual de la factura
                    $valor_retorno = $results[0]->guias_remision_grabar_cabecera;
                    $valor_retorno = trim($valor_retorno, '()');
                    $valor_array = explode(",", $valor_retorno);
                    $input['id'] = $valor_array[0];
                    $input['numero'] = $valor_array[1];

                    //grabamos el detalle de la guia de remision
                    foreach ($input['detalle'] as $detalle) {
                        $results = DB::select(
                            'SELECT guias_remision_grabar_detalle(?,?,?,?,?,?,?)',
                            [
                                $input['documento'],
                                $input['numero'],
                                $input['id'],
                                $detalle['producto_id'],
                                $detalle['descripcion'],
                                $detalle['cantidad'],
                                $input['accion'],
                            ]
                        );
                    };
                }
                else
                {

                    $results = DB::select(
                        'SELECT guias_remision_elimina_detalle(?,?)',
                        [
                            $input['id'],
                            $input['accion'],
                        ]
                    );

                    $results = DB::select(
                        'SELECT guias_remision_elimina_cabecera(?,?,?)',
                        [
                            $input['id'],
                            $input['usuario_id'],
                            $input['accion'],
                        ]
                    );
                }
                //grabar nota contable
                DB::commit();

                return $this->insertOk($input);
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->insertErrCustom($input, $e->getMessage());
            }
        }else{
            return $this->insertErrCustom($validation->messages(), 'Datos inválidos');
        }
    }
}
