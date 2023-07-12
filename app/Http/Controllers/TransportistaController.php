<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Transportista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;


class TransportistaController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function list()
    {
        $list = Transportista::orderBy('id')->get();
        return $this->getOk($list);
    }

    public function list_active()
    {
        $list = Transportista::where('esactivo',1)->get();
        return $this->getOk($list);
    }

    public function getById($id)
    {
      //  $entidad = Transportista::find($id);
      $entidad = Transportista::find($id);
      return $this->getOk($entidad);
    }

    public function delete($id)
    {
        $entidad = Transportista::find($id);
        $entidad->delete();
        return $this->getOk($entidad);
    }

    public function create(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'nombres' => 'required',
                'ruc' => 'required|unique:transportistas,ruc',
                'placa' => 'required',
            ],
            [
                'nombres.required' => 'La razon social es requerido.',
                'ruc.required' => 'El ruc es requerido.',
                'placa.required' => 'La laca es requerida.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $entidad = new Transportista($input);
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
               'nombres' => 'required',
                'ruc' => 'required',
                'placa' => 'required',
            ],
            [
                'nombres.required' => 'La razon social es requerido.',
                'ruc.required' => 'El ruc es requerido.',
                'placa.required' => 'La placa es requerida.',
            ]
        );
        if (!$validation->fails()) {

            $entidad = Transportista::find($request->all()['id']);
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



}
