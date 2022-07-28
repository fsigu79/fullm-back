<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\City;
use App\Models\Country;


class CatalogueController extends Controller
{

    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function cityAll()
    {
        $list = City::orderBy('name', 'asc')->get();
        return $this->getOk($list);
    }

    public function countryAll()
    {
        $list = Country::orderBy('name', 'asc')->get();
        return $this->getOk($list);
    }
}
