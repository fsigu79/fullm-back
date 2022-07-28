<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Observacion;

use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\DB;



class ObservacionController extends Controller
{
    public function __construct() {
          $this -> middleware('api.auth',['except' => [
              'todos',
              'getImage',
              'getObservacionsByGuia',
              'getObservacionsByUser',
              'addImage'
          ]]);
    }

    private function identificate($request) {       
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization',null);
        $user = $jwtAuth->checkToken($token, true);
    return $user;    
    }

    
    public function todos()
    {
        $observaciones = \App\Models\Observacion::all()->load('guia')
                                                       ->load('tipo')
                                                       ->load('user');
        return response() ->json([
            'code' => 200,
            'status'=> 'success',
            'observaciones' => $observaciones
        ]);        
    }  
    
    
    
    public function observacionId($id) 
    {  
        try{
            $observacion = Observacion::find($id);
            /*$observacion = Observacion::find($id) -> load('guia')
                                                    ->load('tipo')
                                                   -> load('user');    */    
             if (is_object($observacion)) {
                 $data = array(
                     'code' => 200,
                     'status' => 'success',
                     'observacion' => $observacion
                 );
             } else {
                 $data = array(
                     'code' => 404,
                     'status' => 'error',
                     'message' => 'Error: Observacion no existe',
                 );            
             }
        }catch(\Exception $e) {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error: la información y sus estado  no se logro conseguir. ',
                'error'     =>  $e,
            );
        }                
        
        return response()->json($data, $data['code']);
    }




    public function crear(Request $request) 
    {
        //RECOGER DATOS POR POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_arrray = json_decode($json,true); //consigo un objeto        
        
        //$params_arrray = array_map('trim', $params_arrray);

        
        if (!empty($params_arrray)) 
        {
            //CONSEGUIR USUARIO IDENTIFICADO
            $user = $this->identificate($request);

            //QUITAR ESPACIOS INCIO Y FIN

            //VALIDAR DATOS
            $validate = \Validator::make($params_arrray, [
                        'tipo_id' => 'required',
                        'guia_id' => 'required',
            ]);
            if ($validate->fails()) {
                //LA VALIDACION A FALLADO
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Observacion no creado',
                    'errors'    => $validate->errors()
                );
            } else {
                try{
                    //CREAR
                    $observacion       = new Observacion();
                    $observacion->user_id      = $user->sub;
                    $observacion->guia_id      = $params->guia_id;                
                    $observacion->tipo_id      = $params->tipo_id;
                    $observacion->contenido    = $params->contenido;
                    $observacion->imagen       = $params->imagen;             
                    $observacion->save();

                    //Confirma en Mensaje
                    $data = array(
                        'code'      => 200,
                        'status'    => 'success',
                        'message'   => 'Observacion creada',
                        'observacion'  => $observacion
                    );
                } catch (\Exception $e){
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
                'message'   => 'No has enviado ninguna Observacion'
            );
        }
    
    return response()->json($data,$data['code']);
    }


   public function modificar(Request $request, $id) {
        //recoger datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            //validar los datos            
            $validate = \Validator::make($params_array, [
               'tipo_id' => 'required',
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
                    unset($params_array['user_id']);
                    unset($params_array['guia_id']);

                    //MODIFICAR REGISTRO            
                    $observacion = Observacion::where('id', $id)->update($params_array);

                    //devolver el array con el resultado
                    $data = array(
                        'code'          => 200,
                        'status'        => 'success',
                        'message'       => 'Se modifico correctamente.',
                        'observacion'      => $params_array,
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
        $observacion = Observacion::find($id);
                
        
        if (!empty($observacion)) {
            //BORRARLO
            $observacion->delete();        
            //DEVOLVER ALGO
            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'observacion'      => $observacion
            );
        }else{
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'No existe el registro.',
            );                        
        }
        
    return response()->json($data, $data['code']);
    }
    


    
    
    public function upload(Request $request) {
        //recoger datos de peticion
        $image = $request->file('file0');

        //validar imagen
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //guardar la imagen
        if (!$image || $validate->fails()) {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen.',
            );
        } else {
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('images')->put($image_name, \File::get($image));
            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name,
            );
        }

    return response()->json($data, $data['code']);
    }

    
    
    public function getImage($filename) {
        $isset = \Storage::disk('images')->exists($filename);
        if ($isset) {
            $file = \Storage::disk('images')->get($filename);
            return new Response($file, 200);
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe',
            );
            return response()->json($data, $data['code']);
        }
    }


    
    public function getObservacionsByGuia($vid) {

               
        try{   
            $sql= " SELECT observaciones.*, 
                    users.email as NombreUsuario,
                    tipos.nombre as NombreTipo
                    FROM observaciones,users,tipos 
                    WHERE observaciones.user_id=users.id
                    AND observaciones.tipo_id=tipos.id
                    AND observaciones.guia_id=? 
                    order by id";
            $observaciones = DB::select($sql,[$vid]);
            
            
            if ($observaciones){
                $data = array(
                    'code'      => 200,
                    'status'    => 'success',
                    'message'   => 'La información y sus estado se consiguió sin problemas.',
                    'observaciones'     => $observaciones,
                );                
            } else {                
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Error: No existe informacion con los parametros dados.',
                );                                            
            }
        }catch(\Exception $e) {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error: la información y sus estado  no se logro conseguir. ',
                'error'     =>  $e,
            );
        }                
        return response()->json($data);
        
    }
      
    public function getObservacionsByUser($id) {
        $observacion = Observacion::where('user_id',$id)->get();
        
        return response()->json([
            'status' => 'success',
            'observaciones'  => $observacion
        ],200);        
    }
    
    public function addImage(Request $request)
    {
        /*
            PARA QUE FUNCIOENTE ESTE CODIGO HAT QUE:
            configurar el fichero httpd.conf y le añado:
            <IfModule mod_headers.c>
              Header set Access-Control-Allow-Origin "*"
            </IfModule>
        */
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $image=$request->file('image');

        if($image){
            $image_path=$image->getClientOriginalName();
           \Storage::disk('images')->put($image_path, \File::get($image));
        }
        $data=array(

           'image'=>$image, 
           'status'=>'success'
       );
       return response()->json($data,200);

    }  

    
    
}
