<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Linea;

class LineaController extends Controller{

    public function __construct() {
        $this->middleware('auth:admin',['except' =>
        [
            'list',
            'all',
        ]]);
    }

    public function all(){
        try{
            $datos= \App\Models\Linea::all();
            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'message'   => 'La información se consiguió sin problemas.',
                'data'      => $datos,
            );
        } catch (\Exception $e) {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error: la información no se logro conseguir: ',
                'error'     =>  $e,
            );
        }
        return response()->json($data);
    }


    public function list(){
        try{
            //$list = User::where('issalesrep', '1')->get();

            $datos= \App\Models\Linea::where('esactivo', '1')->get();
            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'message'   => 'La información se consiguió sin problemas.',
                'data'      => $datos,
            );
        } catch (\Exception $e) {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error: la información no se logro conseguir: ',
                'error'     =>  $e,
            );
        }
        return response()->json($data);
    }


    public function byId($id){
        try{
            $registro = Linea::find($id);
            if (is_object($registro)) {
                $data = array(
                    'code'      => 200,
                    'status'    => 'success',
                    'message'   => 'La información se consiguió sin problemas.',
                    'data' => $registro,
                );
            } else {
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Error: No existe.',
                );
            }
        }catch(\Exception $e) {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error: la información no se logro conseguir: ',
                'error'     =>  $e,
            );
        }
        return response()->json($data, $data['code']);
    }

    public function create(Request $request){
        $json = $request->input('json', null);
        $params_array = json_decode($json,true); //consigo un objeto

        if (!empty($params_array))
        {            
            $validate = \Validator::make($params_array, [
                'nombre'    =>  'required',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Error: La validacion a fallado, revise que los datos requeridos esten completos',
                    'error'     => $validate->errors(),
                );
            } else {
                try{
                    if($params_array['esactivo'] == 'true' || $params_array['esactivo'] == '1'){
                        $vesactivo=1;
                    }else{
                        $vesactivo=0;
                    }

                    $registro              = new Linea();
                    $registro->nombre      = strtoupper($params_array['nombre']);
                    $registro->esactivo    = $vesactivo;                   
                    $registro->save();
                    $data = array(
                        'code'      => 200,
                        'status'    => 'success',
                        'message'   => 'Se creo correctamente.',
                        'data'      => $registro,
                    );

                } catch (\Exception $e){
                    //. $e->getMessage()
                    $data = array(
                        'code'      => 400,
                        'status'    => 'error',
                        'message'   => 'Error: No se pudo crear, existe un conflicto en la base de datos: ',
                        'error'     => $e,
                    );
                }
            }
        } else {
            $data = array(
                'code'      => 404,
                'status'    => 'error',
                'message'   => 'Error: No se ha enviado ninguna informaciónnnn, o la información esta incompleta.',
            );
        }
        return response()->json($data);
    }

    public function update(Request $request, $id){
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $validate = \Validator::make($params_array, [
                'nombre'    =>  'required',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Error: La validacion a fallado, revise que los datos requeridos esten completos',
                    'error'     => $validate->errors(),  
                );
            } else {
                try{
                    unset($params_array['id']);
                    unset($params_array['created_at']);
                    unset($params_array['updated_at']);
                    $registro = Linea::where('id', $id)->update($params_array);
                    $data = array(
                        'code'          => 200,
                        'status'        => 'success',
                        'message'       => 'Se modifico correctamente.',
                        'data'          => $params_array,
                    );
                }catch(\Exception $e){
                    //. $e->getMessage()
                    $data = array(
                        'code'      => 400,
                        'status'    => 'error',
                        'message'   => 'Error: No se pudo modificar, existe un conflicto en la base de datos: ',
                        'error'     =>  $e,
                    );
                }
            }
        }else {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error: No se ha enviado ninguna información, o la información esta incompleta.',
            );
        }
        return response()->json($data, $data['code']);
    }

    public function delete(Request $request, $id){
        //CONSEGUIR USUARIO IDENTIFICADO
        //$user = $this->identificate($request);
        try {
            $registro = Linea::where('id',$id)->first();

            if (!empty($registro)) {
                try {
                    $registro->delete();
                    $data = array(
                        'code'      => 200,
                        'status'    => 'success',
                        'message'   => 'La información se elimino sin problemas.',
                        'data'      => $registro
                    );
                } catch(\Exception $e){
                    $data = array(
                        'code'      => 400,
                        'status'    => 'error',
                        'message'   => 'Imposible de Eliminar, el registro esta asociado a otra información.',
                        'error'     =>  $e,
                    );
                }
            }else{
                $data = array(
                    'code'      => 400,
                    'status'    => 'error',
                    'message'   => 'No existe el registro.',
                );
            }

        }catch(\Exception $e) {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Imposible de Eliminar, no se pudo conseguir la información.',
                'error'     =>  $e,
            );
        }
        return response()->json($data, $data['code']);
    }

    public function search(Request $request){
        try{
            //$parametro=$request->all();
            $registro = Linea::select('id','nombre')
                    ->where('id','like', '%'.$request['codigo'].'%')
                    ->where('nombre' ,'like', '%'.strtoupper($request['nombre']).'%')
                    ->where('esactivo', '1')
                    ->get();   
            if (is_object($registro)) {
                $data = array(
                    'code'      => 200,
                    'status'    => 'success',
                    'message'   => 'La información se consiguió sin problemas.',
                    'data'      => $registro,
                );
            } else {
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Error: No existe.',
                );
            }
        }catch(\Exception $e) {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error: la información no se logro conseguir: ',
                'error'     =>  $e,
            );
        }
        return response()->json($data, $data['code']);
    }





}
