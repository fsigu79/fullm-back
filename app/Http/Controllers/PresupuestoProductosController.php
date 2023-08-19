<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\PresupuestProducto;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Mail;

class PresupuestoProductosController extends Controller
{
    use FormatResponseTrait;


    public function __construct()
    {
        //$this->middleware('auth:admin');
    }


    public function getPresupuestoByAnio(Request $request)
    {
        try {
            $input = $request->all();

            $anio=$input['anio'];
            $marca=$input['marca_id'];

            $sql="SELECT id, anio, marca_id, marca, codigo, descripcion,precio,
                    enero, febrero, marzo, abril, mayo, junio, julio,
                    agosto, septiembre, octubre, noviembre, diciembre,
                    total, total_usd
                FROM presupuesto_productos
                WHERE anio=?
                and case when '0'=? then true else marca_id=? end
                ORDER BY marca_id,descripcion";

            $list = DB::select($sql,[$anio,$marca,$marca]);

            return $this->getOk($list);
        } catch (\Exception $e) {
                DB::rollBack();
                return $this->insertErrCustom('Error', $e->getMessage());
        }

    }


    public function presupuestoCreate(Request $request)
    {
        try {
            $detalle = $request->all();
            DB::beginTransaction();
            $anio=$detalle[0]['anio'];
            DB::delete('DELETE from presupuesto_productos where anio=?',[$anio]);

            $chunks=array_chunk($detalle,75);
            foreach($chunks as $record){
                DB::table('presupuesto_productos')->insert($record);
            }
            DB::commit();
            return $this->insertOk('Guardado correctamente');

        } catch (\Exception $e) {
                DB::rollBack();
                return $this->insertErrCustom('Error', $e->getMessage());
        }

    }




}
