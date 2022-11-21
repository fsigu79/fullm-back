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

    public function listAll()
    {
        $list = Eade::orderBy('anio', 'desc')->orderBy('marca', 'desc');
        return $this->getOk($list);
    }

    public function list(Request $request)
    {
        $anio = $request['anio'];
        $mes = $request['mes'];
        $tipo = $request['tipo'];

        if ($tipo == 'anio') {
            $list = Eade::where('anio', $anio)->orderBy('anio', 'desc')->orderBy('marca', 'desc');
        } elseif ($tipo == 'anio_modelo') {
            $list = Eade::where('anio_modelo', $anio)->orderBy('anio_modelo', 'desc')->orderBy('marca', 'desc');
        } else {
            return $this->getErrCustom($request, 'Tipo de reporte no valido');
        }
        if ($mes != "all") {
            $list = $list->where('mes', $mes);
        }
        $list = $list->get();
        return $this->getOk($list);
    }

    public function listYears(Request $request)
    {
        $tipo = $request['tipo'];
        // if ($tipo == 'anio') {
        //     $sql = "SELECT anio FROM eade GROUP BY anio ORDER BY anio;";
        // } elseif ($tipo == 'anio_modelo') {
        //     $sql = "SELECT anio_modelo FROM eade GROUP BY anio_modelo ORDER BY anio_modelo;";
        // } else {
        //     return $this->getErrCustom($request, 'Tipo de reporte no valido');
        // }
        //$sql = "SELECT ? FROM eade GROUP BY ? ORDER BY ?;";
        //$list = DB::select($sql, [$tipo, $tipo, $tipo]);

        $list = Eade::select($tipo . ' AS anio')->distinct()->orderBy($tipo, 'desc')->get();
        return $this->getOk($list);
    }

    public function searchProductsEade(Request $request)
    {
        $input = $request->all();
        $marca = $request['marca'];
        $modelo = strtoupper($request['modelo']);

        if ($request['marca'] == 'null' || $request['marca'] == '') {
            $marca = '0';
        } else {
            $marca = $request['marca'];
        }

        if ($request['modelo'] == 'null' || $request['modelo'] == '') {
            $modelo = '0';
        } else {
            $modelo = $request['modelo'];
        }

        try {
            $sql = "select distinct codigo, marca,modelo,segmento
                from eade
                where if ('" . $marca . "'='0',true,marca like '%" . $marca . "%') and
                        if ('" . $modelo . "'='0',true,modelo like '%" . $modelo . "%')";

            $list = DB::select($sql);
            return $this->getOk($list);
        } catch (\Exception $e) {
            return $this->insertErrCustom($request, $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        try {
            $input = $request->all();
            //DB::beginTransaction();
            //grabamos el detalle
            foreach ($input['detalle'] as $detalle) {

                $sql = "select count(id) from eade WHERE
                (codigo=?) and
                (anio=?) and
                (mes=?) and
                (anio_modelo=?) and
                (marca=?) and
                (modelo=?) and
                (segmento=?) and
                (provincia=?) and
                (canton=?) and
                (avaluo=?) and
                (unidades=?);";

                $count = DB::select(
                    $sql,
                    [
                        $detalle['codigo'],
                        $detalle['anio'],
                        $detalle['mes'],
                        $detalle['anio_modelo'],
                        $detalle['marca'],
                        $detalle['modelo'],
                        $detalle['segmento'],
                        $detalle['provincia'],
                        $detalle['canton'],
                        $detalle['avaluo'],
                        $detalle['unidades']
                    ]
                );

                #return $this->insertOk($count);

                if ($count[0]->count <= 0) {
                    $entidad = new Eade($detalle);
                    $entidad->save();
                }
            };
            //  DB::commit();
            return $this->insertOk($input);
        } catch (\Exception $e) {
            //DB::rollBack();
            return $this->insertErrCustom(null, $e->getMessage());
        }
    }
}
