<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\DebitoProveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Mail;
use PDF;

class DebitoProveedorController extends Controller
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

        $sql=  "SELECT c.id, documento, numero, proveedor_id,(nombres||' ' ||apellidos) as proveedor,
                        fecha, total,c.saldo,c.esactivo
                FROM compras c
                inner join proveedores p on c.proveedor_id=p.id
                where fecha>=? and fecha<=? and c.documento=?
                order by fecha desc,numero desc";
        $list = DB::select($sql,[$finicio,$ffin,$doc]);

        return $this->getOk($list);
    }

    public function findById($id)
    {
        $invoice = DebitoProvedor::with(['proveedor'])->find($id);
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


                        $results=DB::select('SELECT debito_proveedor_grabar_cabecera(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                                    [$input['id'],
                                    $input['documento'],
                                    $input['numero'],
                                    $input['proveedor_id'],
                                    $input['fecha'],
                                    $input['observacion'],
                                    $input['referencia'],
                                    $input['nota_contable'],
                                    $input['subtotal'],
                                    $input['subcero'],
                                    $input['subiva'],
                                    $input['total'],
                                    $input['saldo'],
                                    $input['esactivo'],
                                    $input['accion']
                                ]);



                        //obtenemos el numero de nota contable y numero actual de la factura
                        $valor_retorno =$results[0]->debito_proveedor_grabar_cabecera;
                        $valor_retorno = trim($valor_retorno, '()');
                        $valor_array = explode (",", $valor_retorno);
                        $input['id']=$valor_array[0];
                        $input['numero']=$valor_array[1];
                        $input['nota_contable']=$valor_array[2];


                    }else{

                        $results=DB::select('SELECT SELECT debito_proveedor_elimina_cabecera(?,?,?,?)',
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
