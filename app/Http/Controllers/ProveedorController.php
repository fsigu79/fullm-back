<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function list()
    {
        $list =Proveedor::with('account:id,codigo')
                ->orderBy('nombres', 'DESC')
                ->orderBy('apellidos', 'DESC')
                ->get();
        return $this->getOk($list);
    }

    public function getRuc($ruc){
        $client = Proveedor::select('id','codigo','ruc','nombres','apellidos','direccion','telefono','celular','contacto','email',
                                    'saldo','cuenta_id','tipo','tipo_compra','tipo_documento','retencion','tiporuc')
                ->where('ruc','=', $ruc)
                ->get();
        return $this->getOk($client);
    }

    public function getById($id)
    {
        $invoice = Proveedor::with(['account'])->find($id);
        return $this->getOk($invoice);
    }

    public function searchProveedor(Request $request)
    {
        $nose=$request->all();
        $list = Proveedor::select('id','codigo','ruc','nombres','apellidos','direccion','telefono','celular','contacto','email',
                                    'saldo','cuenta_id','tipo','tipo_compra','tipo_documento','retencion','tiporuc')
                ->where('nombres','like', '%'.$request['nombres'].'%')
                ->where('apellidos','like', '%'.$request['apellidos'].'%')
                ->where('ruc','like', '%'.$request['ruc'].'%')
                ->get();
        return $this->getOk($list);
    }


    public function create(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'nombres' => 'required',
                'ruc' => 'required',
                'email' => 'required',
            ],
            [
                'nombres.required' => 'El nombre es requerido.',
                'ruc.required' => 'El ruc es requerido.',
                'email.required' => 'El email es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $proveedor = new Proveedor($input);
            $proveedor->save();
            if ($proveedor) {
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
                 'nombres' => 'required',
                'ruc' => 'required',
                'email' => 'required',
            ],
            [
                'nombres.required' => 'El nombre es requerido.',
                'ruc.required' => 'El ruc es requerido.',
                'email.required' => 'El email es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $proveedor = Proveedor::find($request->all()['id']);
            $proveedor->update($request->all());

            if ($proveedor) {
                return $this->updateOk(null);
            } else {
                return $this->updateErr(null);
            }
        } else {
            return $this->updateErrCustom($validation->messages(), 'Datos inválidos');
        }
    }

    public function getDocumentosPendientesByProveedor(Request $request)
    {
        $sql=  "select compras.id,documento||lpad(trim(to_char(numero,'999999999')),9,'0') as documento,fecha,documento_proveedor,total,saldo
                             from compras
                             where proveedor_id=? and saldo>0 and esactivo=1
                             order by fecha";
        $list = DB::select($sql,[$request['proveedor_id']]);

        return $this->getOk($list);
    }


}
