<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Abono;
use App\Models\AbonoDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Mail;
use PDF;

class AbonoController extends Controller
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

        $sql=  "SELECT a.id, documento, numero,
                        cliente_id,(nombre||' ' ||apellido) as cliente,
                        fecha, total,a.esactivo
                FROM abonos a
                inner join clientes c on a.cliente_id=c.id
                where fecha>=? and fecha<=? and a.documento=?
                order by fecha desc,numero desc";
        $list = DB::select($sql,[$finicio,$ffin,$doc]);

        return $this->getOk($list);
    }

    public function findById($id)
    {
        $invoice = PAbonoago::with(['abonoDetalle','clientes'])->find($id);
        return $this->getOk($invoice);
    }

    public function documentosConSaldo($id_cliente)
    {
        $sql=  "select id,documento,numero,documento||serie|| LPAD(numero::text,9,'0')as venta,fecha,fecha_pago,saldo
                from ventas where saldo>0 and cliente_id=?
                order by fecha desc";

        $list = DB::select($sql,[$id_cliente]);

        return $this->getOk($list);
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
        $ret=1;
        if (!$validation->fails()) {
            try{
                    $input = $request->all();
                    DB::beginTransaction();
                    if ($input['accion']!='Eliminar') {
                        //eliminamos el detalle si es modificacion
                        if ($input['accion']==='Modificar') {
                            $results=DB::select('SELECT abonos_elimina_detalle(?,?,?,?)',
                                [$input['id'],
                                $input['documento'],
                                $input['numero'],
                                $input['accion']
                                ]);
                        };

                        $results=DB::select('SELECT abono_grabar_cabecera(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                                    [$input['id'],
                                    $input['serie'],
                                    $input['documento'],
                                    $input['numero'],
                                    $input['cliente_id'],
                                    $input['fecha'],
                                    $input['observacion'],
                                    $input['tipo_pago'],
                                    $input['nota_contable'],
                                    $input['cobrador_id'],
                                    $input['total'],
                                    $input['interes'],
                                    $input['banco_id'],
                                    $input['cheque'],
                                    $input['esactivo'],
                                    $input['accion']
                                ]);



                        //obtenemos el numero de nota contable y numero actual de la factura
                        $valor_retorno =$results[0]->abono_grabar_cabecera;
                        $valor_retorno = trim($valor_retorno, '()');
                        $valor_array = explode (",", $valor_retorno);
                        $input['id']=$valor_array[0];
                        $input['numero']=$valor_array[1];
                        $input['nota_contable']=$valor_array[2];

                        //grabamos el detalle
                        foreach ($input['detalle'] as $detalle) {
                             $results=DB::select('SELECT abono_grabar_detalle(?,?,?,?,?,?,?,?)',[
                            $input['id'],
                            $input['documento'],
                            $input['numero'],
                            $detalle['venta_id'],
                            $detalle['documento_cliente'],
                            $detalle['descripcion'],
                            $detalle['valor'],
                            $detalle['interes']
                            ]);
                        };

                    }else{
                        $results=DB::select('SELECT abonos_elimina_detalle(?,?,?,?)',
                        [$input['id'],
                        $input['documento'],
                        $input['numero'],
                        $input['accion']
                        ]);

                        $results=DB::select('DELETE from abonos where id=?',
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
