<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;



class UseroldController extends Controller {

    public function __construct() {
          $this -> middleware('auth:admin',['except' =>
              [
                  'login',
                  ]]);
    }


    //
    public function pruebas(Request $request) {
        return "Accion user -controller";
    }

    public function register(Request $request) {

        //$name =  $request->input('name');
        //$surname =  $request->input('surname');
        //return "Accion registro de usuarios: $name $surname";
        //var_dump($miObjeto->name);
        //var_dump($miArray);
        //die();
        //recoger datos
        //$json = $request->input('json', null);
        //$miObjeto = json_decode($json); //consigo un objeto
        //$miArray = json_decode($json, true); //consigo un array


        //if (!empty($miObjeto) && !empty($miArray)) {
            //limpiar datos
            //$miArray = array_map('trim', $miArray);
        $validation = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'surname' => 'required',
                    'login' => 'required',
                    'email' => 'required',
                    'password' => 'required',

                ],
                [
                    'documento.required' => 'El documento es requerido.',
                ]
        );
        if ($validation->fails()) {
                //la validacion a fallado
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'usuario no creado',
                    'errors' => $validation->errors()
                );
            } else {
                $miArray = $request->all();

                //cifrar contraseña sha
                $pwd = hash('sha256', $miArray['password']);
                //crear el usuario completo
                $user = new User();
                $user->name = $miArray['name'];
                $user->surname = $miArray['surname'];
                $user->login = $miArray['login'];
                $user->email = $miArray['email'];
                $user->role = $miArray['role'];
                $user->profile_id = $miArray['profile_id'];
                $user->issalesrep = $miArray['issalesrep'];
                $user->iscash = $miArray['iscash'];
                $user->iscollector = $miArray['iscollector'];
                $user->isactive = $miArray['isactive'];
                $user->password = $pwd;

                //guardar el usuario
                $user->save();

                //confirma en mensaje
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'usuario creado',
                    'user' => $user,
                );
            }
       /* } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'datos incorrectos',
            );
        }*/
        return response()->json($data);
    }
    

    public function login(Request $request) {
        $jwtAuth = new \App\Helpers\JwtAuth();
        //recibir datos por post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        //validar datos recibidos por post
        $validate = \Validator::make($params_array, [
                    'login' => 'required', //si existe da un error, valida que sea unico
                    'password' => 'required',
        ]);
        if ($validate->fails()) {
            //la validacion a fallado
            $singup = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'usuario no se ha peodido identificar',
                'errors' => $validate->errors()
            );
        } else {
            //cifrar la contraseña
            $pwd = hash('sha256', $params->password);
            //devolver token o datos
            $singup = $jwtAuth->singup($params->login, $pwd);
            if (isset($params->gettoken)) {
                $singup = $jwtAuth->singup($params->email, $pwd, true);
            }
        }

       return response()->json($singup, 200);
    }

    public function list()
    {
        try{
            $users = User::all();
            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'message'   => 'La información se consiguió sin problemas.',
                'data'  => $users,
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


    public function update(Request $request, $id) {
        //comprobar si el usuario esta identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //recoger datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);


        if ($checkToken && !empty($params_array)) {
            //echo "<h1>login correcto</h1>";
            //sacar ususario identficado
            $user = $jwtAuth->checkToken($token, true);
            //validar los datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'email' => 'required|email|unique:users,' . $user->sub //para acurlizar hace una excepcion
            ]);

            //quitar campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['create_at']);
            unset($params_array['remember_token']);
            //actualziar usuario en BD
            //$user_update = User::where('id', $user->sub)->update($params_array);
            $user_update = User::where('id', $id)->update($params_array);
            //devlver el array con el resultado
            $data = array(
                'status' => 'success',
                'code' => 200,
                'user' => $user,
                'change' => $params_array
            );
        } else {
            //echo "<h1>login INcorrecto</h1>";
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Usuario no esta identificado.',
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
                'imagen' => $image,
            );
        } else {
            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name,
                'image2' => $image->getClientMimeType(),
            );
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename) {
        $isset = \Storage::disk('users')->exists($filename);
        if ($isset) {
            $file = \Storage::disk('users')->get($filename);
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

    public function userId($id) {
        $user = User::find($id);
        if (is_object($user)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user,
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Error: Usuario no existe',
            );
        }
        return response()->json($data, $data['code']);

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
           \Storage::disk('users')->put($image_path, \File::get($image));

    }
         $data=array(

            'image'=>$image,
            'status'=>'success'
        );
        return response()->json($data,200);

}





}
