<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\PuertoEmbarque;
use App\Models\SqlModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PuertosEmbarqueController extends Controller{

    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function list()
    {
        $list = PuertoEmbarque::orderBy('nombre', 'asc')->get();
        return $this->getOk($list);
    }

    public function listActivos()
    {
        $list = PuertoEmbarque::orderBy('nombre', 'asc')->where('esactivo', 1)->get();
        return $this->getOk($list);
    }

    public function findById($id)
    {
        $entidad = PuertoEmbarque::find($id);
        return $this->getOk($entidad);
    }


    public function create(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'nombre' => 'required',
            ],
            [
                'nombre.required' => 'El código del puerto es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $entidad = new PuertoEmbarque($input);
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
                'nombre.required' => 'El código del puerto es requerido',
            ]
        );
        if (!$validation->fails()) {

            $entidad = PuertoEmbarque::find($request->all()['id']);
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

                PuertoEmbarque::where('id', $id)->delete();
                return $this->deleteOk($id);

             } catch (\Exception $e) {
                    return $this->deletetErrCustom($id, $e->getMessage());
                }
        } else {
            return $this->deleteErrCustom($validation->messages(), 'Id invalido');
        }

    }



}
