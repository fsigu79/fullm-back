<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Auditoria;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\FormatResponseTrait;
use Illuminate\Support\Facades\DB;

class AuditoriaController extends Controller{

    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function getByDocumento(Request $request)
    {
        $input = $request->all();
        $sql=  "SELECT modulo, programa, documento_completo, fecha, accion, valor,
                        fecha_documento, usuario_id, usuario_login
                FROM auditorias
                WHERE documento=? and serie=? and numero=?";
        //$list =null;
        $list = DB::select($sql,[$input['documento'],$input['serie'],$input['numero']]);
        return $this->getOk($list);

    }

    public function create($modulo, $programa, $documento,$serie,$numero,$documento_completo, $fecha, $accion, $valor,$fecha_documento, $usuario_id, $usuario_login)
    {
        $entidad= new Auditoria();
        $entidad->agencia_id=1;
        $entidad->modulo=$modulo;
        $entidad->programa=$programa;
        $entidad->documento=$documento;
        $entidad->serie=$serie;
        $entidad->numero=$numero;
        $entidad->documento_completo=$documento_completo;
        $entidad->fecha=$fecha;
        $entidad->accion=$accion;
        $entidad->valor=$valor;
        $entidad->fecha_documento=$fecha_documento;
        $entidad->usuario_id=$usuario_id;
        $entidad->usuario_login=$usuario_login;
        $entidad->pc='pc';
        $entidad->observacion='observa';
        $entidad->save();
        if ($entidad) {
            return true;
        } else {
            return false;
        }

    }


      public function createrequest(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'modulo' => 'required',
                'programa' => 'required',
                'documento' => 'required',
            ],
            [
                'modulo.required' => 'El modulo es requerido.',
                'programa.required' => 'El programa es requerido.',
                'documento.required' => 'El documento es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $entidad = new Auditoria($input);
            $entidad->save();
            if ($entidad) {
                return $this->insertOk(null);
            } else {
                return $this->insertErr(null);
            }
        } else {
            return $this->insertErrCustom($validation->messages(), 'Datos inválidos');
        }
    }


}
