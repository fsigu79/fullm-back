<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Respuesta;

class DashboardController extends Controller
{

    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function data()
    {
        $registered = Respuesta::where('id','>', '0')->get()->count();
        /*$confirmed = Order::where('status', 'C')->get()->count();
        $invoiced = Order::where('status', 'F')->get()->count();
        $dispatched = Order::where('status', 'D')->get()->count();*/
        $confirmed = 7677;
        $invoiced =0;
        $dispatched = 0;
        $data = [
            'registered' => $registered,
            'confirmed' => $confirmed,
            'invoiced' => $invoiced,
            'dispatched' => $dispatched,
        ];
        return $this->getOk($data);
    }
}
