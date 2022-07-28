<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\CuentaGasto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class CuentaGastoController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function list()
    {
        $list = CuentaGasto::orderBy('descripcion', 'asc')->get();
        return $this->getOk($list);
    }

    public function getData($id)
    {
        $product = CuentaGasto::find($id);
        return $this->getOk($product);
    }

    function listPaginate() {
        $list = CuentaGasto::orderBy('descripcion', 'desc')->paginate(10);
        return $this->getOkPagination($list);
    }

    public function store(Request $request)
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

    public function update(Request $request)
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


}
