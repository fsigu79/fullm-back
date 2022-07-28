<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RespuestaExport;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Order;
use App\Models\Respuesta;
use App\Models\Respuesta5;
use App\Models\Respuesta8;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Mail;
use PDF;
use Illuminate\Support\Facades\DB;

class RespuestaController extends Controller
{

    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin',['except' =>
              [
                  'getImage',
                  'addImage'
                  ]]);

    }

    public function all()
    {
        $list = Respuesta::orderBy('pcodigo', 'asc')->get();
        return $this->getOk($list);
    }

    public function getData($id)
    {
        $order = Order::with(['respuesta5', ''])->find($id);
        return $this->getOk($order);
    }




    function comprobar($id)
    {
         $sql= "SELECT c.codigo,c.userid
                    FROM clientes c,users u
                    WHERE c.userid=u.id
                    AND c.codigo=?";
        $cliente = DB::select($sql,[$id]);

         $data = array(
                'code' => 200,
                'status' => 'success',
                'cliente' => $cliente
            );
            return response()->json($data, $data['code']);
    }





    function list(Request $request)
    {
        $input = $request->all();
        $list = Respuesta::with(['client','user'])->orderBy('id', 'desc')->paginate(10);

        return $this->getOkPagination($list);
    }



    public function create(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'pcodigo' => 'required',
            ],
            [
                'pcodigo.required' => 'El codigo del cliente es requerido.',
            ]
        );
            if (!$validation->fails()) {
                try
                {
                    $input = $request->all();
                    $respuesta = new Respuesta($input);
                    $respuesta->save();

                    foreach ($input['respuestas5'] as $parent_row) {
                        $respuesta5 = new Respuesta5($parent_row);
                        $respuesta5->p5codigo = $respuesta->pcodigo;
                        $respuesta5->save();
                    }

                    foreach ($input['respuestas8'] as $parent_row) {
                        $respuesta8 = new Respuesta8($parent_row);
                        $respuesta8->p8codigo = $respuesta->pcodigo;
                        $respuesta8->save();
                    }

                    if ($respuesta) {
                        return $this->insertOk(null);
                    } else {
                        return $this->insertErr(null);
                    }

                 } catch (\Exception $e){
                    return $this->getErr($e);
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
                'client_id' => 'required',
            ],
            [
                'client_id.required' => 'El nombre es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $order = Order::find($input['id']);
            $order->update($request->all());

            $order_boxes = $input['order_boxes'];
            $order_id = $input['id'];
            $all_orders_ids = array_map(function ($order_box) use ($order_id) {
                $new_order_box = OrderBox::updateOrCreate(
                    ['id' => $order_box['id']],
                    [
                        'order_id' => $order_id,
                        'box_id' => $order_box['box_id'],
                        'box_number' => $order_box['box_number'],
                    ],
                );
                $details = $order_box['details'];
                $all_details_ids = array_map(function ($detail) use ($new_order_box) {
                    OrderDetail::updateOrCreate(
                        ['id' => $detail['id']],
                        [
                            'order_box_id' => $new_order_box->id,
                            'product_id' => $detail['product_id'],
                            'longitude' => $detail['longitude'],
                            'price' => $detail['price'],
                            'stems' => $detail['stems'],
                            'total' => $detail['total'],
                            'observation' => $detail['observation'],
                        ],
                    );
                    return $detail['id'];
                }, $details);
                OrderDetail::where('order_box_id', $new_order_box->id)->whereNotIn('id', $all_details_ids)->delete();
                return $order_box['id'];
            }, $order_boxes);
            OrderBox::where('order_id', $order_id)->whereNotIn('id', $all_orders_ids)->delete();

            $order_logs = new OrderLog(
                [
                    'user_id' => $input['user_id'],
                    'order_id' => $order->id,
                    'action' => 'editar'
                ]
            );
            $order_logs->save();

            if ($order) {
                return $this->updateOk(null);
            } else {
                return $this->updateErr(null);
            }
        } else {
            return $this->updateErrCustom($validation->messages(), 'Datos inválidos');
        }
    }

    public function export1(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $input = $request->all();
        return Excel::download(
            new OrderDetailsExport($input['start_at'], $input['end_at']),
            'orden.xlsx'
        );
    }

      public function export(Request $request)
    {
        //return Excel::download(new ProviderExport, 'proveedores.xlsx');
        $input = $request->all();
        return Excel::download(
            new RespuestaExport, 'encuestas.xlsx'
        );
    }

    public function searchAnswer(Request $request)
    {
        $list = Respuesta::with(
            ['respuestas5', 'client', 'respuestas8', 'user']
        )->whereHas('user', function ($query) use ($request) {
            $query->where('username', 'like', '%' . $request['encuestador'] . '%');
        })->where([
            ['pcodigo', 'like', '%' . $request['codigo'] . '%'],
        ]);
        $results = $list->orderBy('pcodigo')->paginate(10);

        return $this->getOkPagination($results);
    }

    public function exportOrder($id)
    {
        $order = Order::with([
            'agency',
            'client',
            'cold_room',
            'provider',
            'orderBoxes',
            'orderBoxes.box',
            'orderBoxes.details',
            'orderBoxes.details.product',
            'user'
        ])
            ->where('id', $id)
            ->get();

        $pdf = PDF::loadView(
            'order',
            compact('order')
        );

        return $pdf->stream('orden.pdf');
    }


    public function getImage($filename) {
        $isset = \Storage::disk('images')->exists($filename);
        if ($isset) {
            $file = \Storage::disk('images')->get($filename);
            return new Response($file, 200);
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe',
            );
            return response()->json($data, $data['code']);
        }
    }



    public function addImage(Request $request)
    {
        /*
            PARA QUE FUNCIOENTE ESTE CODIGO HAY QUE:
            configurar el fichero httpd.conf y le añado:
            <IfModule mod_headers.c>
              Header set Access-Control-Allow-Origin "*"
            </IfModule>
        */
        $image_path='inicio';
        //header('Access-Control-Allow-Origin: *');
        //header('Access-Control-Allow-Headers: *');
        $image=$request->file('image');
        try
        {
            if($image){
                $image_path=$image->getClientOriginalName();
                \Storage::disk('images')->put($image_path, \File::get($image));

            }
            $data=array(
                'image'=>$image,
                'status'=>'success',
                'path'=>$image_path,
            );
        } catch (\Exception $e){
            $data=array(
            'error'=>$e,
            'status'=>'error',
            'code'=>'500',
            );

        }


       return response()->json($data,200);

    }

    public function upload(Request $request) {
        //recoger datos de peticion
        $image = $request->file('file0');

        //validar imagen
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //guardar la imagen
        if (!$image || $validate->fails()) {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen.',
            );
        } else {
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('images')->put($image_name, \File::get($image));
            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name,
            );
        }

    return response()->json($data, $data['code']);
    }






}
