<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin', ['except' => ['login']]);
    }

    public function login(Request $request)
    {
           $validation = Validator::make(
            $request->all(),
            [
                'login' => 'required',
                'password' => 'required',
            ],
            [
                'login.required' => 'Usuario requerido.',
                'password.required' => 'Contraseña requerida.',
            ]
        );
        if ($validation->fails()) {
            return $this->insertErrCustom($validation->messages(), 'Datos inválidos');
        } else {

            $credentials = $request->only(['login', 'password']);
            if (!$token = auth()->guard('admin')->attempt($credentials)) {
                return $this->insertErrCustom($credentials, 'Usuario o contraseña inválidos.');
            }
            $user = User::where('login', $credentials['login'])->first();
            if ($user->isactive == 0) {
                $data = [
                    'token' => null,
                    'status' => $user->isactive,
                ];
                return $this->getOk($data, 'Cuenta desactivada');
            }

            $data = [
                'token' =>$token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 320,
                'user' => $user,
            ];


            return $this->getOk($data);
        }
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 320,
        ]);
    }
}
