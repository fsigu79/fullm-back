<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function all()
    {
        $list = Product::orderBy('code', 'asc')->get();
        return $this->getOk($list);
    }

    public function getData($id)
    {
        $product = Product::find($id);
        return $this->getOk($product);
    }

    function list() {
        $list = Product::orderBy('id', 'desc')->paginate(10);
        return $this->getOkPagination($list);
    }

    public function store(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'code' => 'required',
            ],
            [
                'code.required' => 'El código es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $product = new Product($input);
            $product->save();
            if ($product) {
                return $this->insertOk(null);
            } else {
                return $this->insertErr(null);
            }
        } else {
            return $this->insertErrCustom($validation->messages(), 'Datos inválidos');
        }
    }

    public function update(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'code' => 'required',
            ],
            [
                'code.required' => 'El código es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $product = Product::find($request->all()['id']);
            $product->update($request->all());

            if ($product) {
                return $this->updateOk(null);
            } else {
                return $this->updateErr(null);
            }
        } else {
            return $this->updateErrCustom($validation->messages(), 'Datos inválidos');
        }
    }

    public function searchProducts(Request $request)
    {
        $list = Product::where([
            ['code', 'like', '%' .  $request['code'] . '%'],
            ['description', 'like', '%' . $request['description'] . '%'],
        ]);
        $results = $list->orderBy('code', 'desc')->paginate(10);

        return $this->getOkPagination($results);
    }




}
