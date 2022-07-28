<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Post;
use App\Models\Category;

use App\Helpers\JwtAuth;


class PostController extends Controller
{
    public function __construct() {
          $this -> middleware('api.auth',['except' => [
              'todos',
              'getImage',
              'getPostsByCategory',
              'getPostsByUser'
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
        $posts = \App\Models\Post::all()->load('category');
        
        return response() ->json([
            'code' => 200,
            'status'=> 'success',
            'posts' => $posts
        ]);        
    }  
    
    
    
    public function postId($id) 
    {        
        $post = Post::find($id) -> load('category')
                                -> load('user');
        if (is_object($post)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'post' => $post
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Error: Post no existe',
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
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required',
                        'image' => 'required'
            ]);
            if ($validate->fails()) {
                //LA VALIDACION A FALLADO
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Post no creado',
                    'errors'    => $validate->errors()
                );
            } else {
                //CREA POST
                $post       = new Post();
                $post->user_id      = $user->sub;
                $post->category_id  = $params->category_id;                
                $post->title        = $params->title;
                $post->content      = $params->content;
                $post->image        = $params->image;                
                $post->save();
                
                //Confirma en Mensaje
                $data = array(
                    'code'      => 200,
                    'status'    => 'success',
                    'message'   => 'Post creado',
                    'post'  => $post
                );
            }
        } else {
            //NO SE ENVIO NADA
            $data = array(
                'code'      => 404,
                'status'    => 'error',
                'message'   => 'No has enviado ningun POST'
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
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required'
            ]);
            //Quitar campos que no quiero actualizar
            if($validate->fails()){
                $data['errors'] = $validate->errors();
                return response()->json($data,$data['code']);               
            }
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['create_at']);
            unset($params_array['user']);
            
            //CONSEGUIR USUARIO IDENTIFICADO
            $user = $this->identificate($request);

            //CONSEGUIR EL REGISTRO
            $post = Post::where('id',$id)
                        ->where('user_id',$user->sub)
                        ->first();
            
            if(!empty($post)&& is_object($post)){
                //MODIFICAR REGISTRO
                $post->update($params_array);
                $data = array(
                    'code'      => 200,
                    'status'    => 'success',
                    'post'      => $params_array,
                );
            }else{
                $data = array(
                    'code'      => 400,
                    'status'    => 'error',
                    'message'   => 'El registro no existe',
                );
            }
        } else {
            //echo "<h1>No has enviado ninguna data.</h1>";            
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'No has enviado ningun post.',
            );
        }
        return response()->json($data, $data['code']);
    }



    public function eliminar(Request $request, $id) {        
        //CONSEGUIR USUARIO IDENTIFICADO
        $user = $this->identificate($request);

        //CONSEGUIR EL REGISTRO
        //$post = Post::find($id);
        $post = Post::where('id',$id)
                    ->where('user_id',$user->sub)
                    ->first();
                
        
        if (!empty($post)) {
            //BORRARLO
            $post->delete();        
            //DEVOLVER ALGO
            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'post'      => $post
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


    
    public function getPostsByCategory($id) {
        $post = Post::where('category_id',$id)->get();
        
        return response()->json([
            'status' => 'success',
            'posts'  => $post
        ],200);
        
    }
      
    public function getPostsByUser($id) {
        $post = Post::where('user_id',$id)->get();
        
        return response()->json([
            'status' => 'success',
            'posts'  => $post
        ],200);        
    }
    
    
    
    
}
