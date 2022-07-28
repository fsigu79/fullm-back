<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Box;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BoxController extends Controller
{

    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function all()
    {
        $list = Box::orderBy('name', 'asc')->get();
        return $this->getOk($list);
    }

    public function getData($id)
    {
        $box = Box::find($id);
        return $this->getOk($box);
    }

    function list() {
        $list = Box::orderBy('id', 'desc')->paginate(10);
        return $this->getOkPagination($list);
    }

    public function store(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'name' => 'required',
            ],
            [
                'name.required' => 'El es nombre requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $box = new Box($input);
            $box->save();
            if ($box) {
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
                'name' => 'required',
            ],
            [
                'name.required' => 'El nombre es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $box = Box::find($request->all()['id']);
            $box->update($request->all());

            if ($box) {
                return $this->updateOk(null);
            } else {
                return $this->updateErr(null);
            }
        } else {
            return $this->updateErrCustom($validation->messages(), 'Datos inválidos');
        }
    }

}
