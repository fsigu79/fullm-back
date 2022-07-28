<?php

namespace App\Http\Controllers;

use App\Http\Traits\FormatResponseTrait;
use App\Models\Kardex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;


class KardexController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }


    public function consulta_kardex(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'producto_id' => 'required',
            ],
            [
                'producto_id.required' => 'El producto es requerido.',
            ]
        );
        $ret=1;
        if (!$validation->fails()) {
                try{
                    $input = $request->all();
                    DB::beginTransaction();
                        $results=DB::select('SELECT * from kardex_consulta_articulo(?,?,?)',
                                    [$input['producto_id'],
                                    $input['finicio'],
                                    $input['ffin'],
                                ]);
                    DB::commit();

                    return $this->insertOk($results);

                } catch (\Exception $e) {
                    DB::rollBack();

                    return $this->insertErrCustom($ret, $e->getMessage());
                }

        } else {
            return $this->insertErrCustom($validation->messages(), 'Datos inválidos');
        }
    }



}
