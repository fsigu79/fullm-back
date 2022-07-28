<?php

namespace App\Http\Controllers;

use App\Exports\ClientExport;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }


    public function list()
    {
        $list =Customer::with('account:id,codigo')
                ->orderBy('nombre', 'DESC')
                ->get();
        return $this->getOk($list);
    }

    public function test()
    {
        $list ='nombre';
        return $this->getOk($list);
    }

    public function getRuc($ruc){
        $client = Customer::select('id','codigo','ruc','nombre','apellido','direccion','telefono','celular','contacto','email',
                                    'vendedor_id','precio_id','saldo','anticipo','cupo','dias_credito','cuenta_id')
                ->where('ruc','=', $ruc)
                ->get();
        return $this->getOk($client);
    }

    public function getById($id){
        $client = Customer::select('id','codigo','ruc','nombre','apellido','direccion','telefono','celular','contacto','email',
                                    'vendedor_id','precio_id','saldo','anticipo','cupo','dias_credito','cuenta_id')
                ->where('id','=', $id)
                ->get();
        return $this->getOk($client);
    }

    function list1() {
        $list = Customer::orderBy('ruc', 'desc')->paginate(10);
        return $this->getOkPagination($list);
    }

    public function searchCustomer(Request $request)
    {
        $nose=$request->all();
        $list = Customer::select('id','codigo','ruc','nombre','apellido','direccion','telefono','celular','contacto','email',
                                    'vendedor_id','precio_id','saldo','anticipo','cupo','cuenta_id')
                ->where('nombre','like', '%'.$request['name'].'%')
                ->where('apellido','like', '%'.$request['surname'].'%')
                ->where('ruc','like', '%'.$request['ruc'].'%')
                ->get();
        return $this->getOk($list);
    }

    public function searchCustomer2(Request $request)
    {
        $list = Customer::where([
            ['nombre', 'like', '%'.$request['name'].'%'],
            ['apellido','like','%'.$request['surname'].'%'],
            ['ruc','like','%'.$request['ruc'].'%'],
        ]);
        $results = $list->orderBy('id', 'desc');

        return $this->getOk($results);
    }


    public function create(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'nombre' => 'required',
                'codigo' => 'required',
                'ruc' => 'required',
                'email' => 'required',
            ],
            [
                'nombre.required' => 'El nombre es requerido.',
                'codigo.required' => 'El codigo es requerido.',
                'ruc.required' => 'El ruc es requerido.',
                'email.required' => 'El email es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $customer = new Customer($input);
            $customer->save();
            if ($customer) {
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
                'ruc' => 'required',
                'email' => 'required',
            ],
            [
                'nombre.required' => 'El nombre es requerido.',
                'codigo.required' => 'El codigo es requerido.',
                'ruc.required' => 'El ruc es requerido.',
                'email.required' => 'El email es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $customer = Customer::find($request->all()['id']);
            $customer->update($request->all());

            if ($customer) {
                return $this->updateOk(null);
            } else {
                return $this->updateErr(null);
            }
        } else {
            return $this->updateErrCustom($validation->messages(), 'Datos inválidos');
        }
    }

    public function getDocumentosPendientesByCliente(Request $request)
    {
        $sql=  "select ventas.id,documento||lpad(trim(to_char(numero,'999999999')),9,'0') as documento,fecha,serie||'-'||lpad(trim(to_char(numero,'999999999')),9,'0') as factura,
		                total,saldo
                from ventas
                where cliente_id=? and saldo>0 and esactivo=1
				order by fecha ";
        $list = DB::select($sql,[$request['cliente_id']]);

        return $this->getOk($list);
    }


}
