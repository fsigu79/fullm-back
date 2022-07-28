<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tipo;
use App\Helpers\JwtAuth;

class TipoController extends Controller{
    
    public function __construct() {
          $this -> middleware('api.auth',['except' => ['todos','show']]);
    }
   
    
    private function identificate($request) {       
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization',null);
        $user = $jwtAuth->checkToken($token, true);
        return $user;    
    }

    
    public function todos()
    {
        try{
            $tipos = \App\Models\Tipo::all();
            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'message'   => 'La información se consiguió sin problemas.',
                'tipos'  => $tipos,
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
    
           
    
    public function tipoId($id) 
    {   
        try{
            $tipo = Tipo::find($id);
            if (is_object($tipo)) {
                $data = array(
                    'code'      => 200,
                    'status'    => 'success',
                    'message'   => 'La información se consiguió sin problemas.',
                    'tipo' => $tipo,
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

    
    
    
    
    
    public function crear(Request $request) 
    {
        //RECOGER DATOS POR POST
        $json = $request->input('json', null);
        $params_arrray = json_decode($json,true); //consigo un objeto

        if (!empty($params_arrray)) 
        {
            //QUITAR ESPACIOS INCIO Y FIN
            $params_arrray = array_map('trim', $params_arrray);

            //VALIDAR DATOS
            $validate = \Validator::make($params_arrray, [
                        'nombre'         => 'required',
            ]);
            if ($validate->fails()) {
                //LA VALIDACION A FALLADO
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Error: La validacion a fallado, revise que los datos requeridos esten completos',
                    'error'    => $validate->errors(),
                );
            } else {                             
                try{
                    //PREPARAR DATOS
                    $tipo               = new Tipo();
                    $tipo->nombre       = $params_arrray['nombre'];
                    $tipo->estado       = 1;
                    //CREAR REGISTRO
                    $tipo->save();
                    //Confirma en Mensaje
                    $data = array(
                        'code'      => 200,
                        'status'    => 'success',
                        'message'   => 'Se creo correctamente.',
                        'tipo'  => $tipo,
                    );
                
                } catch (\Exception $e){
                    //. $e->getMessage()
                    $data = array(
                        'code'      => 400,
                        'status'    => 'error',
                        'message'    => 'Error: No se pudo crear, existe un conflicto en la base de datos: ',
                        'error'     =>  $e,
                    );
                }                            
            }
        } else {
            //NO SE ENVIO NADA
            $data = array(
                'code'      => 404,
                'status'    => 'error',
                'message'   => 'Error: No se ha enviado ninguna información, o la información esta incompleta.',
            );
        }    
        return response()->json($data);
    }




    public function modificar(Request $request, $id) {              
        //recoger datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            //validar los datos            
            $validate = \Validator::make($params_array, [
               'nombre' => 'required',
            ]);
            if ($validate->fails()) {
                //LA VALIDACION A FALLADO
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Error: La validacion a fallado, revise que los datos requeridos esten completos',
                    'error'     => $validate->errors(),
                );
            } else {      
                try{
                    //Quitar campos que no quiero actualizar
                    unset($params_array['id']);
                    unset($params_array['created_at']);
                    unset($params_array['updated_at']);       

                    //MODIFICAR REGISTRO            
                    $tipo = Tipo::where('id', $id)->update($params_array);
                    /*
                    $vname= $params_array['name'];
                    $vestado= trim($params_array['estado']);            
                    $tipo = Tipo::find($id);
                    $tipo->nombre = $vname;
                    $tipo->estado = $vestado;            
                    $tipo->save(); 
                    */
                    //devolver el array con el resultado
                    $data = array(
                        'code'          => 200,
                        'status'        => 'success',
                        'message'       => 'Se modifico correctamente.',
                        'tipo'      => $params_array,
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


    
    public function eliminar(Request $request, $id) {        
        //CONSEGUIR USUARIO IDENTIFICADO
        $user = $this->identificate($request);

        //CONSEGUIR EL REGISTRO
        try {
            $tipo = Tipo::where('id',$id)->first();
            
            if (!empty($tipo)) {
                //BORRARLO
                try {
                    //Eliminar registro
                    $tipo->delete();   
                    //DEVOLVER ALGO
                    $data = array(
                        'code'      => 200,
                        'status'    => 'success',
                        'message'   => 'La información se elimino sin problemas.',
                        'tipo'  => $tipo
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
    
    
}
