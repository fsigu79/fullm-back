<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\CuentaGasto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EadeController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function listAll()
    {
        $list = CuentaGasto::orderBy('descripcion', 'asc')->get();
        return $this->getOk($list);
    }


    public function list(Request $request)
    {

        $sql=  "SELECT cg.id, cg.cuenta_id,cg.descripcion, c.codigo as codigo_cuenta,c.nombre
                FROM cuenta_gasto cg
                inner join cuentas c on cg.cuenta_id=c.id
                order by descripcion";
        $list = DB::select($sql);

        return $this->getOk($list);
    }

    public function getById($id)
    {
         $sql=  "SELECT cg.id, cg.cuenta_id,cg.descripcion, c.codigo as codigo_cuenta,c.nombre
                FROM cuenta_gasto cg
                inner join cuentas c on cg.cuenta_id=c.id
                where cg.id=?
                order by descripcion";
        $list = DB::select($sql,[$id]);

        return $this->getOk($list);
    }

    function listPaginate() {
        $list = CuentaGasto::orderBy('descripcion', 'desc')->paginate(10);
        return $this->getOkPagination($list);
    }

    public function create(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'cuenta_id' => 'required',
            ],
            [
                'cuenta_id.required' => 'El código de la cuenta es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $entidad = new CuentaGasto($input);
            $entidad->save();
            if ($entidad) {
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
        $validation = Validator::make(
            $request->all(),
            [
                'cuenta_id' => 'required',
            ],
            [
                'cuenta_id.required' => 'El código de la cuenta es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $entidad = CuentaGasto::find($request->all()['id']);
            $entidad->update($request->all());

            if ($entidad) {
                return $this->updateOk(null);
            } else {
                return $this->updateErr(null);
            }
        } else {
            return $this->updateErrCustom($validation->messages(), 'Datos inválidos');
        }
    }


     public function delete($id) {

        if ($id>0) {
            try{

                CuentaGasto::where('id', $id)->delete();
                return $this->deleteOk($id);

             } catch (\Exception $e) {
                    return $this->insertErrCustom($id, $e->getMessage());
                }
        } else {
            return $this->updateErrCustom($validation->messages(), 'Id invalido');
        }

    }




}
