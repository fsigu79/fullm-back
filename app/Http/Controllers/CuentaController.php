<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\Cuenta;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CuentaController extends Controller{

    use FormatResponseTrait;

    public function __construct() {
         $this->middleware('auth:admin');
    }





    public function list()
    {
        try{
                $sql=  "
                    SELECT cuentas.*, (lpad('',nivel*nivel,' ') || codigo ||' '|| nombre) as nombre_completo
                    FROM cuentas ORDER BY codigo;
                ";
                $data = DB::select($sql);
            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'message'   => 'La información se consiguió sin problemas.',
                'data'  => $data,
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


    public function cuentaByCodigo($codigo)
    {
        try{
            $sql=  "SELECT c.id,c.codigo,nombre,codigo_padre,tipo,estransaccional,esactivo FROM cuentas c where codigo=?  and esactivo=1";
            $data = DB::select($sql,[$codigo]);
            return $this->getOk($data);
        } catch (\Exception $e) {
            return $this->insertErrCustom(null, $e->getMessage());
        }
    }

     public function searchCuentas(Request $request)
    {
        $nose=$request->all();
        $list = Cuenta::select('id','codigo','nombre','codigo_padre','tipo','estransaccional','esactivo')
                ->where('codigo','like', '%'.$request['codigo'].'%')
                ->where('nombre','like', '%'.$request['nombre'].'%')
                ->get();
        return $this->getOk($list);
    }


    public function listAnticipoProvedor()
    {
        try{
                $sql=  "
                    SELECT cuentas.*, (lpad('',nivel*nivel,' ') || codigo ||' '|| nombre) as nombre_completo
                    FROM cuentas where tipo='ANTICIPOP' ORDER BY codigo;
                ";
                $data = DB::select($sql);
            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'message'   => 'La información se consiguió sin problemas.',
                'data'  => $data,
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


    public function listAnticipoCliente()
    {
        try{
                $sql=  "
                    SELECT cuentas.*, (lpad('',nivel*nivel,' ') || codigo ||' '|| nombre) as nombre_completo
                    FROM cuentas where tipo='ANTICIPOC' ORDER BY codigo;
                ";
                $data = DB::select($sql);
            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'message'   => 'La información se consiguió sin problemas.',
                'data'  => $data,
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



    public function create(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'nombre' => 'required',
                'codigo' => 'required',
                'tipo' => 'required',
            ],
            [
                'nombre.required' => 'El nombre es requerido.',
                'codigo.required' => 'El codigo es requerido.',
                'tipo.required' => 'El tipo es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $cuenta = new Cuenta($input);
            $cuenta->save();
            if ($cuenta) {
                return $this->insertOk(null);
            } else {
                return $this->insertErr(null);
            }
        } else {
            return $this->insertErrCustom($validation->messages(), 'Datos inválidos');
        }
    }

    public function edit(Request $request)
    {
        $input=$request->all();
        $validation = Validator::make(
            $request->all(),
            [
                'nombre' => 'required',
                'codigo' => 'required',
                'tipo' => 'required',
            ],
            [
                'nombre.required' => 'El nombre es requerido.',
                'codigo.required' => 'El codigo es requerido.',
                'tipo.required' => 'El tipo es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $cuenta = Cuenta::find($request->all()['id']);
            $cuenta->update($request->all());

            if ($cuenta) {
                return $this->updateOk(null);
            } else {
                return $this->updateErr(null);
            }
        } else {
            return $this->updateErrCustom($validation->messages(), 'Datos inválidos');
        }
    }




}
