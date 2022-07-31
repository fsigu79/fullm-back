<?php

namespace App\Http\Controllers;

use App\Http\Traits\FormatResponseTrait;
use App\Models\Moto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MotoController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function list()
    {
        $list = Moto::with(['marca','modelo','segmento'])->orderBy('marca_id', 'asc')->get();
        return $this->getOk($list);
    }

    public function getById($id)
    {
        $entidad = Moto::with(['marca','modelo','segmento'])->find($id);
        return $this->getOk($entidad);
    }


    public function create(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'marca_id' => 'required',
            ],
            [
                'marca_id.required' => 'La marca es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $entidad = new Moto($input);
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
                'marca_id' => 'required',
            ],
            [
                'marca_id.required' => 'La marca es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $entidad = Moto::find($request->all()['id']);
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

                Moto::where('id', $id)->delete();
                return $this->deleteOk($id);

             } catch (\Exception $e) {
                    return $this->deletetErrCustom($id, $e->getMessage());
                }
        } else {
            return $this->deleteErrCustom($validation->messages(), 'Id invalido');
        }

    }




}
