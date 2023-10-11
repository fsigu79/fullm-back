<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\GuiaRemision;
use App\Models\GuiaRemisionDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class GuiasProductosController extends Controller
{
    use FormatResponseTrait;

    private $sqlgen="Select serie04 as serie,tipotra04 as tipo_transaccion,nocomp04 as documento1,
                        codprod04 as codigo,desprod01 as descripcion,
                        chasis04 as chasis,coddest04 as destino,valor04 as valor,
                        fecmov04 as fecha,
                        (SELECT desccate FROM jcev.categorias WHERE tipocate='02' and codcate = catprod01) as categoria,
                        anio04 as anio,
                        color04 as color,
                        cpn04 as cpn,
                        ramv04,
                        cvanulada04,
                        if (cvanulada04='A','ANULADA', 'ACTIVO') as estado,
                        nofact04 as numero_factura_pac,
                        nopedido04 as pedido,
                        catprod01,
                        notransfer04,
                        nocte31 as cliente_codigo,
                        nomcte31 as cliente,
                        if (cvanulada04='F',(select concat('FAC-',nocomp05) from jcevgyeassem.hisser where serie05=serie04 and tipotra05=80 and fecmov05=(select max(fecmov05) from jcevgyeassem.hisser where  serie05=serie04 and tipotra05=80 )),
                           (select concat('TRA-',nocomp05) from jcevgyeassem.hisser where serie05=serie04 and tipotra05=61 and fecmov05=(select max(fecmov05) from jcevgyeassem.hisser where  serie05=serie04 and tipotra05=61))) as documento
                    from jcevgyeassem.maeser
                    inner join jcevgyeassem.maepro on codprod04=codprod01
                    left join jcevgyeassem.maefac on jcevgyeassem.maefac.nofact31=jcevgyeassem.maeser.nofact04
                    where  fecfact31>=? and fecfact31<=? and (cvanulada04 in ('F','T'))
                    order by documento ASC";

            //fecfact31 antes fecha fecmov04
    public function __construct()
    {
        $this->middleware('auth:admin');
    }



    public function importaCatalogoSeries(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $usuario=Auth::user();
        try{
            $list = DB::connection('mysqlpac')->select($this->sqlgen,[$inicio,$fin,]);

            foreach ($list as $detalle) {

                /*if (is_null($detalle->estado)) {
                    $detalle->estado='0';
                }
                if (is_null($detalle->pedido)) {
                    $detalle->pedido='.';
                }
                if (is_null($detalle->notransfer04)) {
                    $detalle->notransfer04='.';
                }
                if (is_null($detalle->cliente_codigo)) {
                    $detalle->cliente_codigo='.';
                }
                if (is_null($detalle->cliente)) {
                    $detalle->cliente='.';
                }*/
                $results=DB::select('SELECT catalogo_series_grabar(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',[
                                $detalle->serie,
                                $detalle->tipo_transaccion,
                                $detalle->documento,
                                $detalle->codigo,
                                $detalle->descripcion,
                                $detalle->chasis,
                                $detalle->destino,
                                $detalle->valor,
                                $detalle->fecha,
                                $detalle->categoria,
                                $detalle->anio,
                                $detalle->color,
                                $detalle->cpn,
                                $detalle->ramv04,
                                $detalle->cvanulada04,
                                $detalle->estado,
                                $detalle->numero_factura_pac,
                                $detalle->pedido,
                                $detalle->catprod01,
                                $detalle->notransfer04,
                                $detalle->cliente_codigo,
                                $detalle->cliente,
                                $usuario->id,
                                ]);

            }
            $catalogo=DB::select('SELECT * from catalogo_series');
            //guia_remision_numero
            //guia_remision_id
            return $this->getOk($catalogo);

         } catch (\Exception $e) {
            return $this->insertErrCustom($request, $e->getMessage());
        }
    }


    public function obtenerCatalogoSeries(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        try{
            $catalogo=DB::select('SELECT * from catalogo_series');
            return $this->getOk($catalogo);

        } catch (\Exception $e) {
            return $this->insertErrCustom($request, $e->getMessage());
        }
    }



}
