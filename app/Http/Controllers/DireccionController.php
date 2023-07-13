<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Direccion;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\FormatResponseTrait;
use Illuminate\Support\Facades\DB;


class DireccionController extends Controller{

    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function list()
    {

        $list = Direccion::orderBy('nombre', 'asc')->get();
        return $this->getOk($list);
    }

    public function getById($id)
    {
        $entidad = Direccion::find($id);
        return $this->getOk($entidad);
    }

    public function getByCodigoPac($codpac)
    {
        $list = Direccion::where('codigo_cliente_pac', '=',$codpac)->get();
        return $this->getOk($list);
    }

    public function create(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'nombre' => 'required',
            ],
            [
                'nombre.required' => 'El código de la cuenta es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $entidad = new Direccion($input);
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

    public function update(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'nombre' => 'required',
            ],
            [
                'nombre.required' => 'El código de la cuenta es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $entidad = Direccion::find($request->all()['id']);
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

                Direccion::where('id', $id)->delete();
                return $this->deleteOk($id);

             } catch (\Exception $e) {
                    return $this->deletetErrCustom($id, $e->getMessage());
                }
        } else {
            return $this->deleteErrCustom($validation->messages(), 'Id invalido');
        }

    }

}
