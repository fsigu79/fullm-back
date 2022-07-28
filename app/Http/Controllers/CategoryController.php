<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Support\Arr;
use App\Helpers\JwtAuth;

class CategoryController extends Controller{
    
    public function __construct() {
          $this -> middleware('api.auth',['except' => ['index','show']]);
    }

        

    public function pruebas(Request $request){        
        return "Accion category pruebas";
    }
    
    private function identificate($request) {       
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization',null);
        $user = $jwtAuth->checkToken($token, true);
    return $user;    
    }

       
    
    
    
    public function index()
    {
        $categories = \App\Models\Category::all();
        
        return response() ->json([
            'code' => 200,
            'status'=> 'success',
            'categories' => $categories
        ]);        
    }
    
    
    
    
    
    public function categoryId($id) 
    {        
        $categories = Category::find($id);
        if (is_object($categories)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'categories' => $categories,
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Error: Categoria no existe',
            );            
        }
        return response()->json($data, $data['code']);
    }

    
    
    
    
    
    public function crear(Request $request) 
    {
        $date= date('Y-m-d H:i:s');

        //RECOGER DATOS POR POST
        $json = $request->input('json', null);
        $params_arrray = json_decode($json,true); //consigo un objeto

        if (!empty($params_arrray)) 
        {
            //QUITAR ESPACIOS INCIO Y FIN
            $params_arrray = array_map('trim', $params_arrray);

            //VALIDAR DATOS
            $validate = \Validator::make($params_arrray, [
                        'name' => 'required',
            ]);
            if ($validate->fails()) {
                //LA VALIDACION A FALLADO
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Categoria no creada',
                    'errors'    => $validate->errors()
                );
            } else {
                //CREA CATEGORIA
                $category       = new Category();
                $category->name = $params_arrray['name'];
                $category->estado = 'EL';
                $category->fecha_el = $date;
                $category->save();
                //Confirma en Mensaje
                $data = array(
                    'code'      => 200,
                    'status'    => 'success',
                    'message'   => 'Categoria creada',
                    'category'  => $category,
                );
            }
        } else {
            //NO SE ENVIO NADA
            $data = array(
                'code'      => 404,
                'status'    => 'error',
                'message'   => 'No has enviado ninguna categoria',
            );
        }
    
    return response()->json($data);
    }




    public function modificar(Request $request, $id) {
       
        $date= date('Y-m-d H:i:s');
        
        //recoger datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
//                $category->estado = 'PE';
  //              $category->fecha1 = $date;

        if (!empty($params_array)) {
            //validar los datos            
            $validate = \Validator::make($params_array, [
               'name' => 'required',
            ]);
            //Quitar campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['created_at']);
            unset($params_array['updated_at']);       
            
            
            //actualziar usuario en BD            
            /*
            $params_array=Arr::add($params_array, 'estado', 'AM');
            $params_array=Arr::add($params_array, 'fecha1',$date);
            $category = Category::where('id', $id)->update($params_array);
            */
            
            $vname= $params_array['name'];
            $vestado= trim($params_array['estado']);
            $vfecha_el= $params_array['fecha_el'];
            $vfecha_ei= $params_array['fecha_ei'];
            $vfecha_ef= $params_array['fecha_ef'];
            $vfecha_er= $params_array['fecha_er'];
            $vfecha_es= $params_array['fecha_es'];
            
            switch ($vestado) {
                case 'EL':
                       $vestado = "EI";
                       $vfecha_ei=$date;
                       break;
                case 'EI':
                       $vestado = "EF";
                       $vfecha_ef=$date;
                       break;
                case 'EF':
                       $vestado = "ER";
                       $vfecha_er=$date;
                       break;
                case 'ER':
                       $vestado = "ES";
                       $vfecha_es=$date;
                       break;
                case 'ES':
                       //$vestado = "ES";
                       //$vfecha_es=$date;
                       break;
                default :
                       $vestado = "EL";
                       $vfecha_el=$date;
                       break;
            }
            
            $category = Category::find($id);
            $vv=$category->fecha_ef;
            $category->name = $vname;
            $category->estado = $vestado;  
            $category->fecha_el = $vfecha_el;
            $category->fecha_ei = $vfecha_ei;
            $category->fecha_ef = $vfecha_ef;
            $category->fecha_er = $vfecha_er;
            $category->fecha_es = $vfecha_es;
            $category->save();

            
            //devolver el array con el resultado
            $data = array(
                'code'          => 200,
                'status'        => 'success',
                'Actualizacion' => $date,
                'estado'        => $vestado,
                'vv'=> $vv,
                'category'      => $category,
            );

            
        }else {
            //echo "<h1>No has enviado ninguna data.</h1>";            
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'No has enviado ninguna categoria.',
            );
        }
    
    return response()->json($data, $data['code']);
    }


    
    public function eliminar(Request $request, $id) {        
        //CONSEGUIR USUARIO IDENTIFICADO
        $user = $this->identificate($request);

        //CONSEGUIR EL REGISTRO
        $category = Category::where('id',$id)
                    ->first();               
      
        if (!empty($category)) {
            //BORRARLO
            try {
                //Eliminar registro
                $category->delete();   
                //DEVOLVER ALGO
                $data = array(
                    'code'      => 200,
                    'status'    => 'success',
                    'category'      => $category
                );
            } catch (\Exception $e) {
                $data = array(
                    'code'      => 400,
                    'status'    => 'error',
                    'message'    => 'Imposible de Eliminar, el registro esta asociado a otra información.',
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
        
    return response()->json($data, $data['code']);
    }
    

    
    
    
}
