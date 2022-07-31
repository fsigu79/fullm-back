<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Eade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EadeController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function list()
    {
        $list = Eade::orderBy('anio_modelo', 'desc')->orderBy('marca','desc')->get();
        return $this->getOk($list);
    }

    public function create(Request $request)
    {
        try{
            $input = $request->all();
            //DB::beginTransaction();
            //grabamos el detalle
            foreach ($input['detalle'] as $detalle) {
                $entidad = new Eade($detalle);
                $entidad->save();
            };
            //  DB::commit();
            return $this->insertOk($input);

        } catch (\Exception $e) {
            //DB::rollBack();
            return $this->insertErrCustom(null, $e->getMessage());
        }

    }






}
