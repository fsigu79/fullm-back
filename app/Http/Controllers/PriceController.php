<?php

namespace App\Http\Controllers;

use App\Exports\OrderDetailsExport;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\Price;
use App\Models\ProductPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class PriceController extends Controller
{
    use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }


    public function list()
    {
        $list =Price::orderBy('nombre', 'desc')->get();
        return $this->getOk($list);
    }


    public function findById($id)
    {
        $precio = Price::find($id);
        return $this->getOk($precio);
    }

    public function getPrice($product,$price)
    {
        $client = ProductPrice::select('producto_id','precio_id','precio')
                ->where('producto_id','=', $product)
                ->where('precio_id','=', $price)
                ->get();
        return $this->getOk($client);
    }

    public function preciosDetalle(Request $request)
    {
        $input = $request->all();

        // por defecto el id de la lista de precios PVP es 1
        $query=  "SELECT  p.id,p.nombre,p.saldo as existencia,p.costo_promedio,coalesce(a.precio,0) as precio,coalesce(b.precio,0) as pvp,
                            lineas.nombre as linea,grupos.nombre as grupo
                    FROM productos p
                    LEFT JOIN precios_productos a on a.producto_id=p.id and a.precio_id=?
                    LEFT JOIN precios_productos b on b.producto_id=p.id and b.precio_id=1
                    LEFT JOIN lineas on lineas.id=p.linea_id
                    LEFT JOIN grupos on grupos.id=p.grupo_id
                    WHERE case when ?=0 then true else p.linea_id=? end
                    AND  case when ?=0 then true else p.grupo_id=? end
                    ORDER BY p.nombre";
        $list = DB::select($query,[$request['precio_id'],
                                $request['linea_id'],
                                $request['linea_id'],
                                $request['grupo_id'],
                                $request['grupo_id']
                    ]);

        return $this->getOk($list);

    }


 public function save(Request $request)
    {
        try{
            $input = $request->all();
            DB::beginTransaction();
            if ($input['accion']!='Eliminar') {

                //eliminamos el detalle si es modificacion
                if ($input['accion']==='Modificar') {
                    $results=DB::select('DELETE from precios_productos where precio_id=?',
                        [$input['id'],
                        ]);
                };

                $results=DB::select('SELECT precios_grabar_cabecera(?,?,?,?,?,?)',
                            [$input['id'],
                            $input['nombre'],
                            $input['tieneiva'],
                            $input['porcentaje'],
                            $input['esactivo'],
                            $input['accion']
                        ]);

                //obtenemos el numero de nota contable y numero actual de la factura
                $valor_retorno =$results[0]->precios_grabar_cabecera;
                $valor_retorno = trim($valor_retorno, '()');
                $valor_array = explode (",", $valor_retorno);
                $input['id']=$valor_array[0];


                //grabamos el detalle
                $query="INSERT INTO precios_productos(producto_id, precio_id, precio)	VALUES (?, ?, ?);";
                foreach ($input['detalle'] as $detalle) {
                    $results=DB::select($query,[
                    $detalle['producto_id'],
                    $input['id'],
                    $detalle['precio'],
                    ]);
                };

            }else{
                $results=DB::select('DELETE from precios_productos where precio_id=?',
                [$input['id'],
                ]);

                $results=DB::select('DELETE from precios where id=?',
                [$input['id'],
                ]);

            }
            //grabar nota contable
            DB::commit();

            return $this->insertOk($input);

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->insertErrCustom($input, $e->getMessage());
        }

    }


}
