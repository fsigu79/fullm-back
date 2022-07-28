<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Document;
use Illuminate\Support\Facades\Validator;


class DocumentController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }


    public function list()
    {
        $list = Document::orderBy('modulo', 'asc')->get();
        return $this->getOk($list);
    }

    public function getData($id)
    {
        $entidad = Document::find($id);
        return $this->getOk($entidad);
    }

    public function getSeries($code,$module)
    {
        $list = Document::select('id','serie','modulo','numero','codigo_contable')
                ->where('codigo','=', $code)
                ->where('modulo','=', $module)
                ->get();
        return $this->getOk($list);
    }

    public function getDocumentosPlantilla()
    {
        $list = Document::select('id','codigo','modulo','nombre')
                ->where('modulo','<>', 'Contabilidad')
                ->orderBy('modulo','ASC')
                ->get();
        return $this->getOk($list);
    }


    public function getDocumentosContables()
    {
        $list = Document::select('id','codigo','modulo','nombre','numero')
                ->where('modulo','=', 'Contabilidad')
                ->orderBy('modulo','ASC')
                ->get();
        return $this->getOk($list);
    }

    public function create(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'codigo' => 'required',
                'modulo' => 'required',
            ],
            [
                'codigo.required' => 'El código del documento es requerido.',
                'modulo.required' => 'El modulo del documento es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $entidad = new Document($input);
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

    public function edit(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'codigo' => 'required',
                'modulo' => 'required',
            ],
            [
                'codigo.required' => 'El código del documento es requerido.',
                'modulo.required' => 'El modulo del documento es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $entidad = Document::find($request->all()['id']);
            $entidad->update($request->all());

            if ($entidad) {
                return $this->updateOk(null);
            } else {
                return $this->updateErr(null);
            }
        } else {
            return $this->updateErrCustom($validation->messages(), 'Datos inválidos');
        }
    }

}
