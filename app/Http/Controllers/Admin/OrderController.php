<?php
namespace App\Http\Controllers\Admin;

use App\Exports\OrderDetailsExport;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Order;
use App\Models\OrderBox;
use App\Models\OrderDetail;
use App\Models\OrderLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Mail\InvoiceMail;
use Mail;
use PDF;

class   OrderController extends Controller
{

    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function all()
    {
        $list = Order::orderBy('name', 'asc')->get();
        return $this->getOk($list);
    }

    public function getData($id)
    {
        $order = Order::with(['orderBoxes', 'orderBoxes.details'])->find($id);
        return $this->getOk($order);
    }

    function list(Request $request)
    {
        $input = $request->all();
        if ($input['role'] == 1) {
            $list = Order::with(['agency', 'client', 'cold_room', 'provider', 'user'])->orderBy('id', 'desc')->paginate(10);
        }else{
            $results = Order::with(
            ['agency', 'client', 'cold_room', 'provider', 'user']
            )->whereHas('user', function ($query) use ($request) {
                $query->where('user_id', 'like', '%' . $request['id'] . '%');
            });
            $list = $results->orderBy('id', 'desc')->paginate(10);
        }
        return $this->getOkPagination($list);
    }

    public function store(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'client_id' => 'required',
            ],
            [
                'client_id.required' => 'El cliente es requerido.',
            ]
        );
        if (!$validation->fails()) {

            $input = $request->all();
            $order = new Order($input);
            $order->save();

            foreach ($input['order_boxes'] as $parent_row) {
                $order_box = new OrderBox($parent_row);
                $order_box->order_id = $order->id;
                $order_box->save();
                foreach ($parent_row['details'] as $child_row) {
                    $detail = new OrderDetail($child_row);
                    $detail->order_box_id = $order_box->id;
                    $detail->save();
                }
            }

            $order_logs = new OrderLog(
                [
                    'user_id' => $input['user_id'],
                    'order_id' => $order->id,
                    'action' => 'crear'
                ]
            );
            $order_logs->save();

            if ($order) {
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

    public function export(Request $request)
    {
        $input = $request->all();
        return Excel::download(
            new OrderDetailsExport($input['agency'], $input['client'], $input['start_at'], $input['end_at'], $input['role'], $input['id'], $input['status'], $input['report']), 'orden.xlsx'
        );
    }

    public function searchOrders(Request $request)
    {
        $input = $request->all();
       if ($input['role'] == 1 && isset($input['start_at']) && isset($input['end_at'])) {
        $list = Order::with(
            ['agency', 'client', 'cold_room', 'provider', 'user']
        )->whereHas('agency', function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request['agency'] . '%');
        })->whereHas('client', function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request['client'] . '%');
        })->whereBetween('created_at', [$request['start_at'].' 00:00:00', $request['end_at'].' 23:59:00'
        ])->where([
            ['status', 'like', '%' . $request['status'] . '%'],
        ]);
        $results = $list->orderBy('id', 'desc')->paginate(10);

    }elseif(($input['role'] == 1 && !isset($input['start_at']) && !isset($input['end_at']))){
        $list = Order::with(
            ['agency', 'client', 'cold_room', 'provider', 'user']
        )->whereHas('client', function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request['client'] . '%');
        })
        ->where([
            ['observation', 'like', '%' . $request['agency'] . '%'],
        ]);
        $results = $list->orderBy('id', 'desc')->paginate(10);

    }elseif($input['role'] !=1 && !isset($input['start_at']) && !isset($input['end_at'])) {
        $list = Order::with(
            ['agency', 'client', 'cold_room', 'provider', 'user']
        )->whereHas('agency', function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request['agency'] . '%');
        })->whereHas('client', function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request['client'] . '%');
        })->whereHas('user', function ($query) use ($request) {
            $query->where('user_id', 'like', '%' . $request['id'] . '%');
        })->where([
            ['status', 'like', '%' . $request['status'] . '%'],
        ]);
        $results = $list->orderBy('id', 'desc')->paginate(10);
    }else{
        $list = Order::with(
            ['agency', 'client', 'cold_room', 'provider', 'user']
        )->whereHas('agency', function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request['agency'] . '%');
        })->whereHas('client', function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request['client'] . '%');
        })->whereHas('user', function ($query) use ($request) {
            $query->where('user_id', 'like', '%' . $request['id'] . '%');
        })->whereBetween('created_at', [$request['start_at'].' 00:00:00', $request['end_at'].' 23:59:00'
        ])->where([
            ['status', 'like', '%' . $request['status'] . '%'],
        ]);
        $results = $list->orderBy('id', 'desc')->paginate(10);
    }


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

    public function sendOrder($id)
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

        $data['email'] = $order[0]->provider->email;
        $data['subject'] = 'Pedido '.trim($order[0]->client->edr_code).'-'.trim($order[0]->awb);
        //$data['subject'] = 'Pedido '.$order[0]->client->edr_code.'-'.$order[0]->awb;
        //$data['order'] = 'orden-' . str_pad($id, 5, "0", STR_PAD_LEFT) . '.pdf';
        $data['order'] = 'Pedido'.trim($order[0]->client->edr_code).'-'.trim($order[0]->awb).'.pdf';

        $pdf = PDF::loadView(
            'order',
            compact('order')
        );

        try {
            Mail::send('email.invoice', $data, function ($message) use ($data, $id, $pdf) {
                $message->to($data['email'], null)
                    ->from('noreply@ecuadordirectroses.com', 'Ecuador Direct Roses')
                    //->subject($data['subject'] . ' ' . $data['order'])
                    ->subject($data['subject'])
                    ->attachData($pdf->output(), $data['order']);
            });
            return $this->getOkCustom(null, 'Correo enviado exitosamente');
        } catch (\Exception $e) {
            return $this->insertErrCustom(null, $e->getMessage());
        }
    }

    public function exportLabel($id)
    {
        $order = Order::with([
            'agency',
            'client',
            'client.country',
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
            'label',
            compact('order')
        );

        return $pdf->stream('label.pdf');
    }
}
