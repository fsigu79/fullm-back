<?php


namespace App\Http\Controllers;

use App\Http\Traits\FormatResponseTrait;
use App\Http\Controllers\AuditoriaController;
use App\Models\InventarioTransito;
use App\Models\InventarioTransitoDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use DateTime;

class InventarioTransitoController extends Controller
{
     use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function list(Request $request)
    {
        $input = $request->all();
        $finicio=$request['finicio'];
        $ffin=$request['ffin'];
        $doc=$request['doc'];

        $sql=  "SELECT c.id,documento, c.numero,fecha,nombre,observacion,
                       c.esactivo
                FROM inventario_transito c
                where fecha>=? and fecha<=? and c.documento=?
                order by fecha desc,numero desc";

        $list = DB::select($sql,[$finicio,$ffin,$doc]);

        return $this->getOk($list);
    }

    public function findById($id)
    {
        $entidad = InventarioTransito::with(['TransitoDetalle'])->find($id);
        return $this->getOk($entidad);
    }


    public function save(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'nombre' => 'required',
            ],
            [
                'nombre.required' => 'El nombre de la importacion en transito es requerido.',
            ]
        );
        $ret=1;
        if (!$validation->fails()) {
                try{
                    $input = $request->all();
                    $usuario=Auth::user();
                    DB::beginTransaction();
                    if ($input['accion']!='Eliminar') {
                        //eliminamos el detalle si es modificacion
                        if ($input['accion']==='Modificar') {
                            $results=DB::select('SELECT inventario_transito_elimina_detalle(?,?,?,?)',
                                [$input['id'],
                                $input['documento'],
                                $input['numero'],
                                'Modificar'
                                ]);
                        };

                        $results=DB::select('SELECT inventario_transito_grabar_cabecera(?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                                    [$input['id'],
                                    $input['documento'],
                                    $input['numero'],
                                    $input['fecha'],
                                    $input['nombre'],
                                    $input['observacion'],
                                    $input['subtotal'],
                                    $input['subcero'],
                                    $input['subiva'],
                                    $input['total'],
                                    $input['esactivo'],
                                    $usuario->id,
                                    $usuario->login,
                                    $input['accion']
                                ]);

                        //obtenemos el numero de nota contable y numero actual de la factura
                        $valor_retorno =$results[0]->inventario_transito_grabar_cabecera;
                        $valor_retorno = trim($valor_retorno, '()');
                        $valor_array = explode (",", $valor_retorno);
                        $input['id']=$valor_array[0];
                        $input['numero']=$valor_array[1];

                        //grabamos el detalle
                        foreach ($input['detalle'] as $detalle) {
                             $results=DB::select('SELECT inventario_transito_grabar_detalle(?,?,?,?,?,?,?,?)',[
                                    $input['id'],
                                    $input['documento'],
                                    $input['numero'],
                                    $detalle['producto_id'],
                                    $detalle['producto_codigo'],
                                    $detalle['producto_nombre'],
                                    $detalle['cantidad'],
                                    $detalle['tieneiva']
                                    ]);
                        };



                    }else{
                        $results=DB::select('SELECT inventario_transito_elimina_detalle(?,?,?,?)',
                        [$input['id'],
                        $input['documento'],
                        $input['numero'],
                        $input['accion']
                        ]);

                        $results=DB::select('SELECT inventario_transito_elimina_cabecera(?,?,?,?)',
                        [$input['id'],
                        $input['documento'],
                        $input['numero'],
                        $input['accion']
                        ]);

                    }
                     $audi=new AuditoriaController();
                        $resAudi=$audi->Create('Inventarios',
                                                'Inventario Transito',
                                                'IT',
                                                '000',
                                                $input['numero'],
                                                'IT'.str_pad($input['numero'], 9, "0", STR_PAD_LEFT),
                                                new DateTime(),
                                                $input['accion'],
                                                $input['total'] ,
                                                $input['fecha'],
                                                $usuario->id,
                                                $usuario->login);

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
