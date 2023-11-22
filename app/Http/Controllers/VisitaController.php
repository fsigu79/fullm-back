<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Visita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class VisitaController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function all()
    {
        $list = Order::orderBy('id', 'asc')->get();
        return $this->getOk($list);
    }



    public function list(Request $request)
    {
        $input = $request->all();
        $finicio=$request['finicio'];
        $ffin=$request['ffin'];
        $doc=$request['doc'];

        $sql=  "SELECT v.id, numero, cliente_id_pac, cliente, direccion_id,d.nombre as direccion, fecha, tipo_id, v.esactivo
                FROM visitas v
                inner join direcciones d on v.direccion_id=d.id
                where fecha>=? and fecha<=? and v.documento=?
                order by fecha desc";
        $list = DB::select($sql,[$finicio,$ffin,$doc]);

        return $this->getOk($list);
    }

    public function findById($id)
    {
        $entidad = Visita::with(['tipovisita','direciones'])->find($id);
        return $this->getOk($entidad);
    }


    public function save(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'cliente_id_pac' => 'required',
            ],
            [
                'cliente_id_pac.required' => 'El cliente es requerido.',
            ]
        );
        $ret=1;
        if (!$validation->fails()) {
                try{
                    $input = $request->all();
                    DB::beginTransaction();
                    if ($input['accion']!='Eliminar') {
                        $results=DB::select('SELECT visitas_grabar(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                                    [$input['id'],
                                    $input['documento'],
                                    $input['numero'],
                                    $input['cliente_id_pac'],
                                    $input['cliente'],
                                    $input['direccion_id'],
                                    $input['fecha'],
                                    $input['tipo_id'],
                                    $input['contacto'],
                                    $input['observaciones'],
                                    $input['longitud'],
                                    $input['latitud'],
                                    $input['imagen1'],
                                    $input['imagen2'],
                                    $input['imagen3'],
                                    $input['firma'],
                                    $input['revision_stock'],
                                    $input['ex_preferencial'],
                                    $input['material_pop'],
                                    $input['limpieza_producto'],
                                    $input['revision_antiguedad'],
                                    $input['esactivo'],
                                    $input['usuario_created'],
                                    $input['usuario_updated'],
                                    $input['accion']
                                ]);

                    }else{

                        $results=DB::select('SELECT SELECT visita_elimina(?,?,?)',
                        [$input['id'],
                        $input['documento'],
                        $input['numero'],
                        $input['accion']
                        ]);

                    }
                    //grabar nota contable
                    DB::commit();

                    return $this->insertOk($ret);

                } catch (\Exception $e) {
                    DB::rollBack();

                    return $this->insertErrCustom($ret, $e->getMessage());
                }

        } else {
            return $this->insertErrCustom($validation->messages(), 'Datos inválidos');
        }
    }


}
