<?php
namespace App\Http\Controllers;

use App\Exports\OrderDetailsExport;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Mail;
use PDF;


class InvoiceController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function all()
    {
        $list = Order::orderBy('id', 'asc')->get();
        return $this->getOk($list);
    }



    public function list(Request $request)
    {
        $input = $request->all();
        /*$finicio=$request['finicio'].' 00:00:00';
        $ffin=$request['ffin'].' 23:59:00';*/
        $finicio=$request['finicio'];
        $ffin=$request['ffin'];

        $sql=  "SELECT v.id, documento, serie, numero, serie||'-'||LPAD(numero::varchar,9,'0') as factura,
                        cliente_id,(nombre||' ' ||apellido) as cliente,
                        fecha, fecha_pago, total,
                        v.saldo, v.esactivo, autorizado
                FROM ventas v
                inner join clientes c on v.cliente_id=c.id
                where fecha>=? and fecha<=? and v.documento='FE'
                order by fecha desc,factura desc";
        $list = DB::select($sql,[$finicio,$ffin]);

        return $this->getOk($list);
    }

    public function findById($id)
    {
        $invoice = Invoice::with(['invoiceDetail','invoiceDetail.product','customer'])->find($id);
        return $this->getOk($invoice);
    }


    public function save(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'cliente_id' => 'required',
            ],
            [
                'cliente_id.required' => 'El cliente es requerido.',
            ]
        );
        if (!$validation->fails()) {
                try{
                    $input = $request->all();

                    DB::beginTransaction();
                    $results=DB::select('SELECT factura_grabacabecera(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                                [$input['id'],
                                $input['documento'],
                                $input['numero'],
                                $input['serie'],
                                $input['cliente_id'],
                                $input['fecha'],
                                $input['fecha_pago'],
                                $input['precio_id'],
                                $input['referencia'],
                                $input['observacion'],
                                $input['vendedor_id'],
                                $input['nota_contable'],
                                $input['subtotal'],
                                $input['subcero'],
                                $input['subiva'],
                                $input['iva'],
                                $input['iva_porcentaje'],
                                $input['descuento'],
                                $input['total'],
                                $input['costo'],
                                $input['saldo'],
                                $input['escontado'],
                                $input['metodopago_id'],
                                $input['esactivo'],
                                $input['usuario_id'],
                                $input['autorizado'],
                                $input['accion']
                            ]);
                    //obtenemos el numero de nota contable y numero actual de la factura
                    $valor_retorno =$results[0]->factura_grabacabecera;
                    $valor_retorno = trim($valor_retorno, '()');
                    $valor_array = explode (",", $valor_retorno);
                    $input['id']=$valor_array[0];
                    $input['numero']=$valor_array[1];
                    $input['nota_contable']=$valor_array[2];

                    //grabamos el detalle
                    if ($input['accion']=='Modificar'){
                        DB::select('DELETE from ventasd where venta_id=?',[$input['id']]);
                    }

                    foreach ($input['detalle'] as $detalle) {
                        $det = new InvoiceDetail();
                        //$det->venta_id = $valor_retorno[0];
                        $det->venta_id = $input['id'];
                        $det->producto_id=$detalle['producto_id'];
                        $det->documento=$input['documento'];
                        $det->descripcion = $detalle['descripcion'];
                        $det->cantidad = $detalle['cantidad'];
                        $det->tieneiva = $detalle['tieneiva'];
                        $det->descuento = $detalle['descuento'];
                        $det->descuentop = $detalle['descuentop'];
                        $det->costo = $detalle['costo'];
                        $det->precio = $detalle['precio'];
                        $det->save();
                    }

                    //grabar nota contable

                    DB::commit();
                    return $this->insertOk($input);

                } catch (\Exception $e) {
                    DB::rollBack();

                    return $this->insertErrCustom($input, $e->getMessage());
                }

        } else {
            return $this->insertErrCustom($validation->messages(), 'Datos inválidos');
        }
    }





}
