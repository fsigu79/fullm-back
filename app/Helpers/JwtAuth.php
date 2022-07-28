<?php
namespace App\Helpers;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class JwtAuth
{

    public $key;
    public function __construct() {
        $this -> key = 'uda24868';
    }


    public function singup($login,$password,$getToken = null) {
        //buscar usuario con sus credenciales
            $user = User::Where([
                'login' =>$login,
                'password' =>$password,
            ])->first();


        //comprobar crenciales correctas
            $singup = false;
            if(is_object($user)){
                $singup = true;
            };
        //generara el token con los datos del suuario identificado
            if($singup)
            {
                $token = array(
                    'sub'       => $user->id,
                    'login'     => $user->login,
                    'email'     => $user->email,
                    'name'      => $user->name,
                    'surname'   => $user->surname,
                    'isactive'   => $user->isactive,
                    'description'  => $user->description,
                    'image'   => $user->image,
                    'profileid'   => $user->profile_id,
                    'role'   => $user->role,
                    'iat'       => time(),
                    'exp'       => time()+(7*24*60*60) // dura una semana
                );
                $jwt     =JWT::encode($token, $this->key, 'HS256');
                $decoded =JWT::decode($jwt, $this->key,  ['HS256']);

        //devolver los datos decodificados o el token, en funcion del un parametro
                if(is_null($getToken)){
                    //$data = $jwt;
                $data=array(
                    'status' => 'success',
                    'message' => 'Login correcto',
                    'token' => $jwt,
                    'user' => $token,
                    );
                }else{
                     $data=array(
                    'status' => 'error',
                    'message' => 'Login a fallado....',
                    'token' => $decoded,
                );

                }
            }else{
                $data=array(
                    'status' => 'error',
                    'message' => 'Login a fallado',
                );
            }

    return $data;
    }

    public function checkToken($jwt,$getIdentity=false){
        $auth = false;
        try{
            $jwt= str_replace('"','',$jwt);
            $decode=JWT::decode($jwt,$this->key,['HS256']);
        } catch(\UnexpectedValueException $e){
            $auth=false;
        } catch(\DomainException $e){
            $auth=false;
        }

        if(!empty($decode)&& is_object($decode)&&isset($decode->sub)){
            $auth=true;
        }else{
            $auth=false;
        }
        if($getIdentity){
            return $decode;
        }

    return $auth;
    }




}
