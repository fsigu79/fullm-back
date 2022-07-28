<?php

namespace App\Http\Controllers;
use App\Exports\OrderDetailsExport;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Anticipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class AnticipoController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function list(Request $request)
    {
        $input = $request->all();
        $finicio=$request['finicio'];
        $ffin=$request['ffin'];
        $doc=$request['doc'];

        $sql=  "SELECT a.id, documento, numero, proveedor_id,(nombres||' ' ||apellidos) as proveedor,
                        fecha, total,a.esactivo
                FROM anticipos a
                inner join proveedores p on a.proveedor_id=p.id
                where fecha>=? and fecha<=? and a.documento=?
                order by fecha desc,numero desc";
        $list = DB::select($sql,[$finicio,$ffin,$doc]);

        return $this->getOk($list);
    }

    public function findById($id)
    {
        $invoice = Anticipo::with(['proveedor','cuenta','banco'])->find($id);
        return $this->getOk($invoice);
    }


    public function create(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'proveedor_id' => 'required',
                'cuenta_id' => 'required',
            ],
            [
                'proveedor_id.required' => 'El proveedor es requerido.',
                'cuenta_id.required' => 'La cuenta es requerida.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $anticipo = new Anticipo($input);
            $anticipo->save();
            if ($anticipo) {
                return $this->insertOk($anticipo);
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
                'proveedor_id' => 'required',
                'cuenta_id' => 'required',
            ],
            [
                'proveedor_id.required' => 'El proveedor es requerido.',
                'cuenta_id.required' => 'La cuenta es requerida.',
            ]
        );
        if (!$validation->fails()) {

            $anticipo = Anticipo::find($request->all()['id']);
            $anticipo->update($request->all());

            if ($anticipo) {
                return $this->updateOk($anticipo);
            } else {
                return $this->updateErr(null);
            }
        } else {
            return $this->updateErrCustom($validation->messages(), 'Datos inválidos');
        }
    }



}
