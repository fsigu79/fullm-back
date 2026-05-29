<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\VisitaVendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VisitaVendedorController extends Controller
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

        $usuario=Auth::user();
        $role=$usuario->role;
        $user=$usuario->id;

        $sql=  "SELECT v.id, numero, cliente_id_pac, cliente, direccion_id,d.nombre as direccion, fecha,
                        tipo_id, v.esactivo,v.longitud,v.latitud,inicio,fin,
                        u.name||' '||u.surname as vendedor
                FROM visitasv v
                inner join direcciones d on v.direccion_id=d.id
                left join users u on usuario_created=u.id
                where fecha>=? and fecha<=? and v.documento=?
                and case when '3'!=? then true else usuario_created=? end
                order by numero desc";
        $list = DB::select($sql,[$finicio,$ffin,$doc,$role,$user]);

        return $this->getOk($list);
    }




    public function findById($id)
    {
        $entidad = VisitaVendedor::with(['tipovisita','direciones'])->find($id);
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
        if (!$validation->fails()) {
                $input = $request->all();
                try{
                    $usuario=Auth::user();
                    $userc=$usuario->id;
                    $userm=$usuario->id;

                    DB::beginTransaction();
                    if ($input['accion']!='Eliminar') {
                          $results=DB::select('SELECT visitas_vendedor_grabar(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
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
                                    $input['escobranza'],
                                    $input['pagare'],
                                    $input['pedido_numero'],
                                    $input['inicio'],
                                    $input['fin'],
                                    $input['esactivo'],
                                    $userc,
                                    $userm,
                                    $input['accion'],

                                ]);

                    }else{

                        $results=DB::select('SELECT SELECT visita_vendedor_elimina(?,?,?)',
                        [$input['id'],
                        $input['documento'],
                        $input['numero'],
                        $input['accion']
                        ]);

                    }
                    //grabar nota contable
                    DB::commit();

                    return $this->insertOk($input);

                } catch (\Exception $e) {
                    DB::rollBack();

                    return $this->insertErrCustom($input, $e->getMessage());
                }

        } else {
            return $this->insertErrCustom($validation->messages(), 'Datos inválidos');
        }
    }


}
