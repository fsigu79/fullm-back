<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Destino;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\FormatResponseTrait;

class DestinoController extends Controller{

    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }


    public function list()
    {
        $list = Destino::orderBy('nombre', 'asc')->get();
        return $this->getOk($list);
    }

    public function getData($id)
    {
        $product = Destino::find($id);
        return $this->getOk($product);
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
            $entidad = new Destino($input);
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

            $entidad = Destino::find($request->all()['id']);
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



}
