<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Transportista;

class UserController extends Controller
{

    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin',['except' =>
              [
                  'login',
                  ]]);
    }




    public function all()
    {
        $list = User::orderBy('name', 'asc')->get();
        return $this->getOk($list);
    }


    public function getData($id)
    {
        $user = User::where('id', $id)->first();
        return $this->getOk($user);
    }


    public function salesrep()
    {
       $list = User::where('issalesrep', '1')->get();
        return $this->getOk($list);
    }

    public function cobradorrep()
    {
       $list = User::where('iscollector', '1')->get();
        return $this->getOk($list);
    }


    public function list()
    {
        /*$list = User::with([
            'role' => function ($query) {
                $query->select(['id', 'name']);
            },
        ]);*/

        $list = User::select(['id', 'name']);


        $results = $list->orderBy('id', 'desc')->paginate(10);

        return $this->getOkPagination($results);
    }

    public function searchUsers(Request $request)
    {
        $list = User::with([
            'role' => function ($query) {
                $query->select(['id', 'name']);
            },
        ])->whereHas('role', function ($query) use ($request) {
            $query->where('id', 'like', '%' . $request['role_id'] . '%');
        })->where(
            \DB::raw("CONCAT(
                coalesce(`name`, ''), ' ',
                coalesce(`surname`, '')
            )"),
            'LIKE',
            '%' . $request['full_name'] . '%'
        )->where([
            ['username', 'like', '%' . $request['username'] . '%'],
            ['status', 'like', '%' .  $request['status'] . '%']
        ]);
        $results = $list->orderBy('id', 'desc')->paginate(10);

        return $this->getOkPagination($results);
    }

    public function register(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'surname' => 'required|alpha',
                'login' => 'required|alpha',
                'email' => 'required|unique:users',
                'password' => 'required',
            ],
            [
                'name.required' => 'El nombre es requerido.',
                'surname.required' => 'El apellido es requerido.',
                'login.required' => 'El login es requerido.',
                'email.required' => 'El email es requerido.',
                'password.required' => 'La clave del usuario es requerida.',

            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $user = new User($input);
            //$user->auid = (string) \Str::uuid();
            $user->save();

             //Se obtiene el id del resgistro de la tabla de usuarios
             $latestId = User::latest('id')->first()->id;

             //Comparamos los roles para crear el rol de transportista
             if($request['role'] == "2"){
                 $validation_t = Validator::make(
                     $request->all(),
                     [
                         'razon_social' => 'required|unique:transportistas,razon_social',
                         'ruc' => 'required|unique:transportistas,ruc',
                         'placa' => 'required',
                     ],
                     [
                         'razon_social.required' => 'La razon social es requerido.',
                         'ruc.required' => 'El ruc es requerido.',
                         'placa.required' => 'La laca es requerida.',
                     ]
                 );
                 if (!$validation_t->fails()) {

                     $input = $request->all();
                     $entidad = new Transportista();
                     $entidad->razon_social = $request['razon_social'];
                     $entidad->chofer = $request['chofer'];
                     $entidad->placa = $request['placa'];
                     $entidad->ruc = $request['ruc'];
                     $entidad->esactivo = $request['esactivo'];
                     $entidad->user_id= $latestId;
                     $entidad->save();
                      if (!$entidad) {
                     //     return $this->insertOk($entidad);
                     // } else {
                         return $this->insertErr(null);
                     }
                 } else {
                     return $this->insertErrCustom($validation_t->messages(), 'Datos inválidos');
                 }
             }

            if ($user) {
                return $this->insertOk(null);
            } else {
                return $this->insertErr(null);
            }
        } else {
            return $this->insertErrCustom($validation->messages(), 'Datos inválidos');
        }
    }

     public function register1(Request $request) {

        //$name =  $request->input('name');
        //$surname =  $request->input('surname');
        //return "Accion registro de usuarios: $name $surname";
        //var_dump($miObjeto->name);
        //var_dump($miArray);
        //die();
        //recoger datos
        $json = $request->input('json', null);
        $miObjeto = json_decode($json); //consigo un objeto
        $miArray = json_decode($json, true); //consigo un array

        if (!empty($miObjeto) && !empty($miArray)) {
            //limpiar datos
            $miArray = array_map('trim', $miArray);

            //validar datos
            $validate = \Validator::make($miArray, [
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'login' => 'required|alpha',
                        'email' => 'required|unique:users', //si existe da un error, valida que sea unico
                        'role' => 'required',
                        'password' => 'required',
            ]);

            if ($validate->fails()) {
                //la validacion a fallado
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'usuario no creado',
                    'errors' => $validate->errors()
                );
            } else {

                //cifrar contraseña sha
                $pwd = hash('sha256', $miObjeto->password);
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
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'datos incorrectos',
            );
        }
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

    public function update(Request $request,$id)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'surname' => 'required',
            ],
            [
                'surname.required' => 'El nombre es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $user = User::find($id);

            if (isset($input["password"]) && $input["password"] != "" && $input["password"] != null) {
                $user["password"] = Hash::make($input["password"]);
                //return $this->updateOkCustom('user1',$user);
            } else {
                unset($input["password"]);
                //return $this->updateOkCustom('user2',$user);
            }

            $user->update($input);

            try{
                if ($user) {
                    return $this->updateOk(null);
                } else {
                    return $this->updateErr(null);
                }
            }catch(\Exception $e){
                return $this->updateErrMsg($e);
            }



        } else {
            //return $this->updateErrCustom($validation->messages(), 'Datos inválidos');
            return $this->updateErrCustom($$input1, 'Datos invalidos');
        }
    }

    public function userId($id) {
        $user = User::find($id);
        if (is_object($user)) {
            if($user->role == '2'){
                $transportista = Transportista::where('user_id',$user->id)->get();
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'user' => $user,
                    'transportista'=> $transportista[0]
                );
            }else{
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'user' => $user,
                );
            }
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Error: Usuario no existe',
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

    public function changePassword(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'old_password' => ['required'],
                'auid' => ['required'],
                'password' => ['required', 'confirmed'],
            ],
            [
                'old_password.required' => 'Contraseña anterior requerida.',
                'auid.required' => 'Identificador requerido.',
                'password.required' => 'Contraseña requerida.',
                'password.confirmed' => 'Las contraseñas no son iguales.',
            ]
        );
        if ($validation->fails()) {
            return $this->updateErrCustom($validation->messages(), 'Datos inválidos');
        } else {
            $user = User::where('auid', $request->input('auid'))->first();
            if (isset($user->id)) {
                if (Hash::check($request->input('old_password'), $user->password)) {
                    $user->password = $request['password'];
                    $user->save();
                    return $this->getOkCustom(null, 'Su contraseña a sido cambiada.');
                } else {
                    return $this->updateErrCustom(null, 'La contraseña anterior es incorrecta');
                }
            } else {
                return $this->updateErrCustom(null, 'Identificador inválido');
            }
        }
    }
}
