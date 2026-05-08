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
        $list = Transportista::on('pgsql_optimus')->orderBy('id')->get();

        return $this->getOk($list);
    }

    public function list_active()
    {
        // Aplicamos lo mismo para el filtro de activos
        $list = Transportista::on('pgsql_optimus')
            ->where('esactivo', 1)
            ->get();

        return $this->getOk($list);
    }

    public function getById($id)
    {
        // Buscamos el registro específicamente en la conexión pgsql_optimus
        $entidad = Transportista::on('pgsql_optimus')->find($id);

        if ($entidad) {
            return $this->getOk($entidad);
        } else {
            // Opcional: manejar el error si no existe el transportista
            return $this->getOk(null);
        }
    }

    public function delete($id)
    {
        // Buscamos el registro en la conexión pgsql_optimus
        $entidad = Transportista::on('pgsql_optimus')->find($id);

        if ($entidad) {
            $entidad->delete(); // Al haber sido cargado con on(), el delete() se ejecuta en esa misma conexión
            return $this->getOk($entidad);
        } else {
            return $this->updateErrCustom(null, 'El transportista no existe.');
        }
    }

    public function create(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'nombres' => 'required',
                // Indicamos a la validación que busque el unique en la conexión pgsql_optimus
                'ruc' => 'required|unique:pgsql_optimus.transportistas,ruc',
                'placa' => 'required',
            ],
            [
                'nombres.required' => 'La razon social es requerido.',
                'ruc.required' => 'El ruc es requerido.',
                'placa.required' => 'La placa es requerida.',
            ]
        );

        if (!$validation->fails()) {
            $input = $request->all();

            // Instanciamos el modelo y definimos la conexión antes de guardar
            $entidad = new Transportista();
            $entidad->setConnection('pgsql_optimus');
            $entidad->fill($input);
            $entidad->save();

            return $entidad ? $this->insertOk($entidad) : $this->insertErr(null);
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
            // Usamos on() para buscar el registro en la conexión correcta
            $entidad = Transportista::on('pgsql_optimus')->find($request->id);

            if ($entidad) {
                $entidad->update($request->all());
                return $this->updateOk($entidad);
            } else {
                return $this->updateErr(null);
            }
        } else {
            return $this->updateErrCustom($validation->messages(), 'Datos inválidos');
        }
    }


}
