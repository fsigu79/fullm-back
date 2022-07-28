<?php

namespace App\Http\Controllers;
use App\Http\Traits\FormatResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Banco;

class BancoController extends Controller
{

    use FormatResponseTrait;

    public function __construct() {
         $this->middleware('auth:admin');
    }

    public function list()
    {
        $list = Banco::orderBy('nombre', 'asc')->get();
        return $this->getOk($list);
    }

    public function getData($id)
    {
        $product = banco::find($id);
        return $this->getOk($product);
    }


}
