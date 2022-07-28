<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\SriTabla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SriController extends Controller
{
     use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function listRetencion1($tabla)
    {
        $list = SriTabla::where('tabla', '=', $tabla);
        $results = $list->orderBy('codigo', 'asc');

        return $this->getOk($results);
    }


    public function listRetencion($tabla)
    {
        $sql=  "SELECT tabla,codigo,concepto,valor FROM sritabla where tabla='".$tabla."' ORDER BY codigo";
        $data = DB::select($sql);

        return $this->getOk($data);
    }




}
