<?php

namespace App\Http\Controllers;

use App\Http\Traits\FormatResponseTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\Asiento;
use App\Models\AsientoDetalle;
use Illuminate\Support\Facades\DB;


class AsientoController extends Controller
{
    use FormatResponseTrait;

    public function __construct() {
         $this->middleware('auth:admin');
    }



    public function list(Request $request)
    {
        $input = $request->all();
        /*$finicio=$request['finicio'].' 00:00:00';
        $ffin=$request['ffin'].' 23:59:00';*/
        $finicio=$request['finicio'];
        $ffin=$request['ffin'];

        $sql=  "SELECT id, documento,LPAD(numero::varchar,9,'0') as numero, beneficiario, fecha, modulo
                from asientos
                where fecha>=? and fecha<=?
                order by documento,fecha desc";
        $list = DB::select($sql,[$finicio,$ffin]);

        return $this->getOk($list);
    }


    public function getByDocNum(Request $request)
    {
        $input = $request->all();
        $doc=$request['documento'];
        $num=$request['numero'];

        $list = Asiento::with(['asientoDetalle','asientoDetalle.cuenta'])
            ->where('documento','=',$request['documento'])
            ->where('numero','=',$request['numero'])
            ->get();

        return $this->getOk($list);
    }



    public function findById($id)
    {
        $asiento = Asiento::with(['asientoDetalle','asientoDetalle.cuenta'])->find($id);
        return $this->getOk($asiento);
    }

    public function save(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'documento' => 'required',
            ],
            [
                'documento.required' => 'El documento es requerido.',
            ]
        );

        if (!$validation->fails()) {
                try{
                    $input = $request->all();
                    DB::beginTransaction();

                    if ($input['accion']!='Eliminar') {

                        //eliminamos el detalle si es modificacion
                        if ($input['accion']==='Modificar') {
                            $results=DB::select('SELECT asientos_elimina(?,?,?,?,?)',
                                [$input['id'],
                                $input['documento'],
                                $input['numero'],
                                $input['fecha'],
                                $input['accion']
                                ]);
                        };

                        $results=DB::select('SELECT asientos_graba_cabecera(?,?,?,?,?,?,?,?,?,?,?,?)',
                                    [$input['id'],
                                    $input['documento'],
                                    $input['numero'],
                                    $input['beneficiario'],
                                    $input['fecha'],
                                    $input['descripcion'],
                                    $input['referencia'],
                                    $input['modulo'],
                                    $input['graba'],
                                    $input['usuario_id'],
                                    $input['esactivo'],
                                    $input['accion'],
                                ]);



                        //obtenemos el numero de nota contable y numero actual de la factura
                        $valor_retorno =$results[0]->asientos_graba_cabecera;
                        $valor_retorno = trim($valor_retorno, '()');
                        $valor_array = explode (",", $valor_retorno);
                        $input['id']=$valor_array[0];
                        $input['numero']=$valor_array[1];


                        //grabamos el detalle
                        foreach ($input['detalle'] as $detalle) {
                             $results=DB::select('SELECT asientos_graba_detalle(?,?,?,?,?,?,?,?,?)',[
                            $input['id'],
                            $input['documento'],
                            $input['numero'],
                            $detalle['cuenta_id'],
                            $detalle['cuenta_codigo'],
                            $detalle['observacion'],
                            $detalle['debe'],
                            $detalle['haber'],
                            $detalle['fecha'],
                            ]);
                        };



                    }else{
                        $results=DB::select('SELECT asientos_elimina(?,?,?,?,?)',
                                [$input['id'],
                                $input['documento'],
                                $input['numero'],
                                $input['fecha'],
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
