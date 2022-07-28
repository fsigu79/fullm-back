<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\DebitoCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DebitoClienteController extends Controller
{
    //Clase para mensajes de retorno 
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function all()
    {
        $list = DebitoCliente::orderBy('id', 'asc')->get();
        return $this->getOk($list);
    }

    public function list(Request $request)
    {
        $input = $request->all();  //para validar el json de entrada 
        $finicio=$request['finicio'];  
        $ffin=$request['ffin'];
        $doc=$request['doc'];

        $sql=  "SELECT c.id, documento, numero, cliente_id,(nombre||' ' ||apellido) as cliente,
                        fecha, total,c.saldo,c.esactivo
                FROM ventas c
                inner join clientes p on c.cliente_id=p.id
                where fecha>=? and fecha<=? and c.documento=?
                order by fecha desc,numero desc";
        $list = DB::select($sql,[$finicio,$ffin,$doc]);

        return $this->getOk($list);
    }

    public function findById($id)
    {
        $debito = DebitoCliente::with(['cliente'])->find($id);
        return $this->getOk($debito);
    }


    public function save(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'cliente_id' => 'required',
                'fecha' => 'required',
                'fecha_pago'=> 'required'
            ],
            [
                'cliente_id.required' => 'El cliente es requerido.',
                'fecha.required' => 'La fecha es requerido.',
                'fecha_pago.required' => 'La fecha de pago es requerido.'
            ]
        );

        if (!$validation->fails()) {
                try{
                    $input = $request->all();
                    DB::beginTransaction();
                    if ($input['accion']!='Eliminar') {
                        //eliminamos el detalle si es modificacion


                        $results=DB::select('SELECT debito_cliente_grabar_cabecera(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                                    [$input['id'],
                                    $input['documento'],
                                    $input['numero'],
                                    $input['cliente_id'],
                                    $input['fecha'],
                                    $input['fecha_pago'], 
                                    $input['referencia'], 
                                    $input['vendedor_id'],
                                    $input['nota_contable'],
                                    $input['subtotal'],
                                    $input['subcero'],
                                    $input['subiva'],
                                    $input['iva'],
                                    $input['iva_porcentaje'],
                                    $input['total'],
                                    $input['saldo'],
                                    $input['esactivo'],
                                    $input['observacion'],
                                    $input['usuario_id'],
                                    $input['accion']
                                    
                                ]);

                        //obtenemos el numero de nota contable y numero actual de la factura
                        $valor_retorno =$results[0]->debito_cliente_grabar_cabecera;
                        $valor_retorno = trim($valor_retorno, '()');
                        $valor_array = explode (",", $valor_retorno);
                        $input['id']=$valor_array[0];
                        $input['numero']=$valor_array[1];
                        $input['nota_contable']=$valor_array[2];


                    }else{

                        $results=DB::select('SELECT  debito_cliente_elimina_cabecera(?,?,?,?,?)',
                        [$input['id'],
                        $input['documento'],
                        $input['numero'],
                        $input['accion'],
                        $input['usuario_id']
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
