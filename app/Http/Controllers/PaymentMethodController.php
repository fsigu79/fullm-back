<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use Illuminate\Support\Facades\Validator;
use App\Models\PaymentMethod;

class PaymentMethodController extends Controller
{

       use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function all()
    {
        $list = PaymentMethod::orderBy('codigo_sri', 'asc')
        ->where('esactivo','=', '1')
        ->get();
        return $this->getOk($list);
    }

    public function getById($id)
    {
        $paymnet = PaymentMethod::find($id);
        return $this->getOk($paymnet);
    }




}
