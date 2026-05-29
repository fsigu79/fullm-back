<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\TipoVisita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class TipoVisitaController extends Controller
{

    use FormatResponseTrait;

    public function __construct() {
        $this->middleware('auth:admin');
    }

    public function all(){
        try{
            $list = \App\Models\TipoVisita::all();
            return $this->getOk($list);

        } catch (\Exception $e) {
           return $this->getErr($e->getMessage());
        }

    }


    public function list(){
        try{
        $list = TipoVisita::where('esactivo', '1')->where('modulo','P')->get();
            return $this->getOk($list);

        } catch (\Exception $e) {
            return $this->getErr($e->getMessage());
        }

    }

    public function listVisitas(){
        try{
        $list = TipoVisita::where('esactivo', '1')->where('modulo','V')->get();
            return $this->getOk($list);

        } catch (\Exception $e) {
            return $this->getErr($e->getMessage());
        }

    }

    public function getById($id){
        try{
            $entidad = TipoVisita::find($id);
           return $this->getOk($entidad);

        }catch(\Exception $e) {
            return $this->getErrCustom($entidad, $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'nombre' => 'required',
            ],
            [
                'nombre.required' => 'El nombre es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $entidad = new TipoVisita($input);
            $entidad->save();
            if ($entidad) {
                return $this->insertOk($entidad);
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
               'nombre' => 'required',
            ],
            [
                'nombre.required' => 'El nombre es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $entidad = TipoVisita::find($request->all()['id']);
            $entidad->update($request->all());

            if ($entidad) {
                return $this->updateOk($entidad);
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

                TipoVisita::where('id', $id)->delete();
                return $this->deleteOk($id);

             } catch (\Exception $e) {
                    return $this->deletetErrCustom($id, $e->getMessage());
                }
        } else {
            return $this->deleteErrCustom($validation->messages(), 'Id invalido');
        }

    }


}
