<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\GastoJcev;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class GastoJcevController extends Controller
{
    //Clase para mensajes de retorno
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function all()
    {
        $list = GastoJcev::orderBy('id', 'asc')->get();
        return $this->getOk($list);
    }

    public function list(Request $request)
    {
        $input = $request->all();  //para validar el json de entrada
        $finicio=$request['finicio'];
        $ffin=$request['ffin'];
        $doc=$request['doc'];

        $sql=  "SELECT c.id, documento, numero, cliente_id,tipo,observacion,
                        fecha, total,c.esactivo
                FROM gastos_jcev c
                where fecha>=? and fecha<=? and c.documento=?
                order by fecha desc,numero desc";
        $list = DB::select($sql,[$finicio,$ffin,$doc]);

        return $this->getOk($list);
    }

    public function findById($id)
    {
        $entidad = GastoJcev::find($id);
        return $this->getOk($entidad);
    }


    public function save(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'tipo' => 'required',
                'fecha' => 'required',
            ],
            [
                'tipo.required' => 'El cliente es requerido.',
                'fecha.required' => 'La fecha es requerido.',
            ]
        );

        if (!$validation->fails()) {
                try{
                    $input = $request->all();
                    DB::beginTransaction();
                    if ($input['accion']!='Eliminar') {
                        //eliminamos el detalle si es modificacion

                        $results=DB::select('SELECT gastos_jcev_grabar(?,?,?,?,?,?,?,?,?,?,?)',
                                    [$input['id'],
                                    $input['documento'],
                                    $input['numero'],
                                    $input['cliente_id'],
                                    $input['fecha'],
                                    $input['observacion'],
                                    $input['tipo'],
                                    $input['total'],
                                    $input['esactivo'],
                                    $input['usuario_id'],
                                    $input['accion']
                                ]);

                        //obtenemos el numero de nota contable y numero actual de la factura
                        $valor_retorno =$results[0]->gastos_jcev_grabar;
                        $valor_retorno = trim($valor_retorno, '()');
                        $valor_array = explode (",", $valor_retorno);
                        $input['id']=$valor_array[0];
                        $input['numero']=$valor_array[1];

                    }else{

                        $results=DB::select('SELECT gastos_jcev_elimina(?,?,?,?,?)',
                        [$input['id'],
                        $input['documento'],
                        $input['numero'],
                        $input['accion'],
                        $input['usuario_id']
                        ]);

                    }
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

    public function delete($id) {
        if ($id>0) {
            try{
                GastoJcev::where('id', $id)->delete();
                return $this->deleteOk($id);

             } catch (\Exception $e) {
                    return $this->deletetErrCustom($id, $e->getMessage());
                }
        } else {
            return $this->deleteErrCustom($validation->messages(), 'Id invalido');
        }

    }


}
