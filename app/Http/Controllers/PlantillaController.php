<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Plantilla;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PlantillaController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function getDocumentosPlantilla(Request $request)
    {
        $input = $request->all();
        $list = Plantilla::where('documento','=',$request['documento'])
                ->where('modulo',$request['modulo'])
                ->orderBy('id','ASC')
                ->get();
        return $this->getOk($list);
    }


    public function save(Request $request)
    {
        try{
            $input = $request->all();
            DB::beginTransaction();
            $results=DB::select('delete from plantillas where documento=? and modulo =?',[$input['documento'],$input['modulo']]);

            //grabamos el detalle
            $sql = "INSERT INTO plantillas(documento, modulo, descripcion, nombre, cuenta_id, tipo, esactivo, status)
	                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            foreach ($input['detalle'] as $detalle) {
                $results=DB::select($sql,[
                $input['documento'],
                $input['modulo'],
                $detalle['descripcion'],
                $detalle['nombre'],
                $detalle['cuenta_id'],
                $detalle['tipo'],
                $detalle['esactivo'],
                $detalle['status'],
                ]);
            };

            //grabar nota contable
            DB::commit();
            return $this->insertOk("ok");

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->insertErrCustom($ret, $e->getMessage());
        }
    }



}
