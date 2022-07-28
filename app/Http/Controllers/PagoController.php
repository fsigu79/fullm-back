<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Pago;
use App\Models\PagoDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Mail;
use PDF;

class PagoController extends Controller
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

        $sql=  "SELECT c.id, documento, numero,
                        proveedor_id,(nombres||' ' ||apellidos) as proveedor,
                        fecha, total,c.esactivo
                FROM pagos c
                inner join proveedores p on c.proveedor_id=p.id
                where fecha>=? and fecha<=? and c.documento=?
                order by fecha desc,numero desc";
        $list = DB::select($sql,[$finicio,$ffin,$doc]);

        return $this->getOk($list);
    }

    public function findById($id)
    {
        $invoice = Pago::with(['PagoDetalle','pagoDetalle.product','proveedor'])->find($id);
        return $this->getOk($invoice);
    }

    public function documentosConSaldo($id_proveedor)
    {
        $sql=  "select id,documento,numero,documento|| LPAD(numero::text,9,'0')as compra,documento_proveedor,fecha,fecha_pago,saldo
                from compras where saldo>0 and proveedor_id=?
                order by fecha_pago desc";

        $list = DB::select($sql,[$id_proveedor]);

        return $this->getOk($list);
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
                            $results=DB::select('SELECT pagos_elimina_detalle(?,?,?,?)',
                                [$input['id'],
                                $input['documento'],
                                $input['numero'],
                                $input['accion']
                                ]);
                        };

                        $results=DB::select('SELECT pagos_grabar_cabecera(?,?,?,?,?,?,?,?,?,?,?,?,?)',
                                    [$input['id'],
                                    $input['documento'],
                                    $input['numero'],
                                    $input['proveedor_id'],
                                    $input['fecha'],
                                    $input['observacion'],
                                    $input['tipo_pago'],
                                    $input['nota_contable'],
                                    $input['total'],
                                    $input['banco_id'],
                                    $input['cheque'],
                                    $input['esactivo'],
                                    $input['accion']
                                ]);



                        //obtenemos el numero de nota contable y numero actual de la factura
                        $valor_retorno =$results[0]->pagos_grabar_cabecera;
                        $valor_retorno = trim($valor_retorno, '()');
                        $valor_array = explode (",", $valor_retorno);
                        $input['id']=$valor_array[0];
                        $input['numero']=$valor_array[1];
                        $input['nota_contable']=$valor_array[2];

                        //grabamos el detalle
                        foreach ($input['detalle'] as $detalle) {
                             $results=DB::select('SELECT pagos_grabar_detalle(?,?,?,?,?,?,?)',[
                            $input['id'],
                            $input['documento'],
                            $input['numero'],
                            $detalle['compra_id'],
                            $detalle['documento_proveedor'],
                            $detalle['descripcion'],
                            $detalle['valor']
                            ]);
                        };

                    }else{
                        $results=DB::select('SELECT pagos_elimina_detalle(?,?,?,?)',
                        [$input['id'],
                        $input['documento'],
                        $input['numero'],
                        $input['accion']
                        ]);

                        $results=DB::select('DELETE from pagos where id=?',
                        [$input['id'],
                        ]);

                    }
                    //grabar nota contable
                    DB::commit();

                    return $this->insertOk($input);

            } catch (\Exception $e) {
                DB::rollBack();
                return $this->insertErrCustom($ret, $e->getMessage());
            }

        } else {
            return $this->insertErrCustom($validation->messages(), 'Datos inválidos');
        }
    }




}
