<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Product;


class ProductController extends Controller{
    use FormatResponseTrait;

    public function __construct(){
        $this->middleware('auth:admin',['except' =>
        [
            'list',
            'all',
            'getImage',
            'addImage'
        ]]);
    }

    public function all(){
        try{
            $products = \App\Models\Product::all();
            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'message'   => 'La información se consiguio sin problemas.',
                'data'  => $products,
            );
        } catch (\Exception $e) {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error: la información no se logro conseguir: ',
                'error'     =>  $e,
            );
        }
        return response()->json($data);
    }
    public function list(){
        try{
            $products = \App\Models\Product::where('esactivo', '1')->get();
            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'message'   => 'La información se consiguio sin problemas.',
                'data'  => $products,
            );
        } catch (\Exception $e) {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error: la información no se logro conseguir: ',
                'error'     =>  $e,
            );
        }
        return response()->json($data);
    }

    public function productId($id){
        try{
            $product = Product::find($id);
            if (is_object($product)) {
                $data = array(
                    'code'      => 200,
                    'status'    => 'success',
                    'message'   => 'La información se consiguió sin problemas.',
                    'data' => $product,
                );
            } else {
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Error: No existe.',
                );
            }
        }catch(\Exception $e) {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error: la información no se logro conseguir: ',
                'error'     =>  $e,
            );
        }
        return response()->json($data, $data['code']);
    }
    // funcion para crear
    public function create(Request $request){
        //RECOGER DATOS POR POST
        $json = $request->input('json', null);
        $params_array = json_decode($json,true); //consigo un objeto

        if (!empty($params_array))
        {
            //QUITAR ESPACIOS INCIO Y FIN
            //$params_array = array_map('trim', $params_array);

            //VALIDAR DATOS
            $validate = \Validator::make($params_array, [
                        'nombre'         => 'required',
                        'linea_id'       => 'required',
                        'grupo_id'       => 'required',
            ]);
            if ($validate->fails()) {
                //LA VALIDACION A FALLADO
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Error: La validacion a fallado, revise que los datos requeridos esten completos',
                    'error'    => $validate->errors(),
                );
            } else {
                try{
                    //PREPARAR DATOS
                    if($params_array['esactivo'] == 'true' || $params_array['esactivo'] == '1'){
                        $vesactivo=1;
                    }else{
                        $vesactivo=0;
                    }

                    if($params_array['inventariado'] == 'true' || $params_array['inventariado'] == '1'){
                        $vinventariado=1;
                    }else{
                        $vinventariado=0;


                    }

                    if($params_array['tieneiva'] == 'true' || $params_array['tieneiva'] == '1'){
                        $vtieneiva=1;
                    }else{
                        $vtieneiva=0;
                    }


                    $product                        = new Product();
                    $product->codigo                = $params_array['codigo'];
                    $product->barcode               = $params_array['barcode'];
                    $product->nombre                = $params_array['nombre'];
                    $product->linea_id              = $params_array['linea_id'];
                    $product->grupo_id              = $params_array['grupo_id'];
                    $product->cuenta_inventario     = $params_array['cuenta_inventario'];
                    $product->cuenta_costo          = $params_array['cuenta_costo'];
                    $product->cuenta_venta          = $params_array['cuenta_venta'];
                    $product->cuenta_ventades       = $params_array['cuenta_ventades'];
                    $product->cuenta_ventadev       = $params_array['cuenta_ventadev'];
                    $product->cuenta_comprades      = $params_array['cuenta_comprades'];
                    $product->cuenta_compradev      = $params_array['cuenta_compradev'];
                    $product->descuento             = $params_array['descuento'];
                    $product->existencia_minima     = $params_array['existencia_minima'];
                    $product->imagen                = $params_array['imagen'];
                    $product->esactivo              = $vesactivo;
                    $product->inventariado          = $vinventariado;
                    $product->tieneiva              = $vtieneiva;
                    $product->save();
                    $data = array(
                        'code'      => 200,
                        'status'    => 'success',
                        'message'   => 'Se creo correctamente',
                        'data'      => $product,
                    );

                } catch (\Exception $e){
                    //. $e->getMessage()
                    $data = array(
                        'code'      => 400,
                        'status'    => 'error',
                        'message'    => 'Error-> No se pudo crear, existe un conflicto en la base de datos: ',
                        'error'     =>  $e,
                    );
                }
            }
        } else {
            //NO SE ENVIO NADA
            $data = array(
                'code'      => 404,
                'status'    => 'error',
                'message'   => 'Error: No se ha enviado ninguna información, o la información esta incompleta.',
            );
        }
        return response()->json($data);
    }

    public function update(Request $request, $id){
        //recoger datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            //validar los datos
            $validate = \Validator::make($params_array, [
                'nombre'         => 'required',
                'linea_id'       => 'required',
                'grupo_id'       => 'required',
            ]);
            if ($validate->fails()) {
                //LA VALIDACION A FALLADO
                $data = array(
                    'code'      => 404,
                    'status'    => 'error',
                    'message'   => 'Error: La validacion a fallado, revise que los datos requeridos esten completos',
                    'error'     => $validate->errors(),
                );
            } else {
                try{
                    //Quitar campos que no quiero actualizar
                    unset($params_array['id']);
                    unset($params_array['created_at']);
                    unset($params_array['updated_at']);

                    //MODIFICAR REGISTRO
                    $product = Product::where('id', $id)->update($params_array);
                    //devolver el array con el resultado
                    $data = array(
                        'code'          => 200,
                        'status'        => 'success',
                        'message'       => 'Se modifico correctamente.',
                        'data'          => $params_array,
                    );
                }catch(\Exception $e){
                    //. $e->getMessage()
                    $data = array(
                        'code'      => 400,
                        'status'    => 'error',
                        'message'   => 'Error: No se pudo modificar, existe un conflicto en la base de datos: ',
                        'error'     =>  $e,
                    );
                }
            }
        }else {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error: No se ha enviado ninguna información, o la información esta incompleta.',
            );
        }
        return response()->json($data, $data['code']);
    }

    public function delete(Request $request, $id){
        //CONSEGUIR USUARIO IDENTIFICADO
        //$user = $this->identificate($request);

        try {
            $product = Product::where('id',$id)->first();

            if (!empty($product)) {
                try {
                    $product->delete();
                    $data = array(
                        'code'      => 200,
                        'status'    => 'success',
                        'message'   => 'La información se elimino sin problemas.',
                        'data'      => $product
                    );
                } catch(\Exception $e){
                    $data = array(
                        'code'      => 400,
                        'status'    => 'error',
                        'message'   => 'Imposible de Eliminar, el registro esta asociado a otra información.',
                        'error'     =>  $e,
                    );
                }
            }else{
                $data = array(
                    'code'      => 400,
                    'status'    => 'error',
                    'message'   => 'No existe el registro.',
                );
            }

        }catch(\Exception $e) {
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Imposible de Eliminar, no se pudo conseguir la información.',
                'error'     =>  $e,
            );
        }
        return response()->json($data, $data['code']);
    }

    public function getImage($filename){
        $isset = \Storage::disk('images')->exists($filename);
        //echo($filename);
        //echo($isset);
        if ($isset) {
            $file = \Storage::disk('images')->get($filename);
            return Response($file, 200);
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe',
            );
            return response()->json($data, $data['code']);
        }
    }

    public function addImage(Request $request){
        /*
            PARA QUE FUNCIOENTE ESTE CODIGO HAY QUE:
            configurar el fichero httpd.conf y le añado:
            <IfModule mod_headers.c>
              Header set Access-Control-Allow-Origin "*"
            </IfModule>
        */
        //header('Access-Control-Allow-Origin: *');
        //header('Access-Control-Allow-Headers: *');

        $image=$request->file('image');

        if($image){
            $image_path=$image->getClientOriginalName();
           \Storage::disk('images')->put($image_path, \File::get($image));
        }
        $data=array(

           'image'=>$image,
           'status'=>'success'
       );
       return response()->json($data,200);

    }

    public function searchProducts(Request $request){
        $nose=$request->all();
        $list = Product::select('id','codigo','nombre','barcode','descripcion','saldo','costo_promedio','descuento','inventariado','tieneiva')
                ->where('codigo','like', '%'.$request['code'].'%')
                ->where('nombre','like', '%'.strtoupper($request['name']).'%')
                ->where('esactivo', '1')
                ->get();
        return $this->getOk($list);
    }

}
