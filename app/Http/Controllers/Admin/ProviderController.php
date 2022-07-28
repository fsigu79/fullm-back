<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProviderExport;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ProviderController extends Controller
{

    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function all()
    {
        $list = Provider::orderBy('name', 'asc')->get();
        return $this->getOk($list);
    }

    public function getData($id)
    {
        $provider = Provider::find($id);
        return $this->getOk($provider);
    }

    function list() {
        $list = Provider::with(['city'])->orderBy('id', 'desc')->paginate(10);
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
                'name.required' => 'El nombre es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $provider = new Provider($input);
            $provider->save();
            if ($provider) {
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

            $provider = Provider::find($request->all()['id']);
            $provider->update($request->all());

            if ($provider) {
                return $this->updateOk(null);
            } else {
                return $this->updateErr(null);
            }
        } else {
            return $this->updateErrCustom($validation->messages(), 'Datos inválidos');
        }
    }

    public function export() 
    {
        return Excel::download(new ProviderExport, 'proveedores.xlsx');
    }

    public function searchProviders(Request $request)
    {
        $list = Provider::with([
            'city' => function ($query) {
                $query->select(['id', 'name']);
            },
        ])->whereHas('city', function($query) use ($request) {
            $query->where('name', 'like', '%' . $request['city'] . '%');
        })->where([
            ['name', 'like', '%' .  $request['name'] . '%'],
            ['email', 'like', '%' . $request['email'] . '%']
        ]);
        $results = $list->orderBy('id', 'desc')->paginate(10);

        return $this->getOkPagination($results);
    }

}
