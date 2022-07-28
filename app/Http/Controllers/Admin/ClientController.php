<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ClientExport;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ClientController extends Controller
{

    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function all()
    {
        $list = Client::orderBy('nombre', 'asc')->get();
        return $this->getOk($list);
    }

    public function getData($id)
    {
        $client = Client::find($id);
        return $this->getOk($client);
    }

    public function getCode($id)
    {
        $client = Client::where('codigo','=', $id)->get();;
        //$client = Client::find($id);
        return $this->getOk($client);
    }

    function list() {
        $list = Client::orderBy('codigo', 'desc')->paginate(10);
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
            $client = new Client($input);
            $client->save();
            if ($client) {
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

            $client = Client::find($request->all()['id']);
            $client->update($request->all());

            if ($client) {
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
        return Excel::download(new ClientExport, 'clientes.xlsx');
    }

    public function searchClients(Request $request)
    {
        $list = Client::whereHas('country', function($query) use ($request) {
            $query->where('name', 'like', '%' . $request['country'] . '%');
        })->where([
            ['name', 'like', '%' .  $request['name'] . '%'],
            ['edr_code', 'like', '%' . $request['edr_code'] . '%'],
            ['status', 'like', '%' . $request['status'] . '%'],
        ]);
        $results = $list->orderBy('id', 'desc')->paginate(10);

        return $this->getOkPagination($results);
    }

}
