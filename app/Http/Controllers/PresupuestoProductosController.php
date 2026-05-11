<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\PresupuestProducto;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Mail;

class PresupuestoProductosController extends Controller
{
    use FormatResponseTrait;


    public function __construct()
    {
        //$this->middleware('auth:admin');
    }


    public function getPresupuestoByAnio(Request $request)
    {
        try {
            $input = $request->all();

            $anio=$input['anio'];
            $marca=$input['marca_id'];
            $vendedor=$input['vendedor_id'];
            $cliente=$input['cliente_id'];

           //--productos menos los de categoria I=importaciones,G=gastos,9=servicios
            $query=  "select codprod01 as codigo,desprod01 as producto,marca01 as marca_id,
                        (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab='4530' AND codtab<>'' AND codtab=jcev.maepro.marca01) AS marca
                    from jcev.maepro where tipprod01='S' AND statuspro01='S' and catprod01 not in('I','G','9')
                    order by desprod01";
            $list = DB::connection('mysqlpac')->select($query);

            //DB::beginTransaction();
            //DB::delete('DELETE from productos_pac');

            /*foreach($list as $produ){
                $presprod=new PresupuestProducto();
                $presprod->anio=$produ->anio;
                $presprod->marca_id=$produ->anio;
                $presprod->marca=$produ->anio;
                $presprod->cod_cliente=$produ->anio;
                $presprod->cliente=$produ->anio;
                $presprod->vendedor_id=$produ->anio;
                $presprod->codigo=$produ->anio;
                $presprod->descripcion=$produ->anio;
                $presprod->precio=$produ->anio;
                $presprod->enero=$produ->anio;
                $presprod->febrero=$produ->anio;
                $presprod->marzo=$produ->anio;
                $presprod->abril=$produ->anio;
                $presprod->mayo=$produ->anio;
                $presprod->junio=$produ->anio;
                $presprod->julio=$produ->anio;
                $presprod->agosto=$produ->anio;
                $presprod->septiembre=$produ->anio;
                $presprod->octubre=$produ->anio;
                $presprod->noviembre=$produ->anio;
                $presprod->diciembrediciembrediciembre=$produ->anio;
                $presprod->total=$produ->anio;
                $presprod->total_usd=$produ->anio;

                  DB::table('presupuesto_productos')->updateOrInsert(
                    ['anio' => $produ->anio, 'codigo' => $produ->codigo],
                    $produ);
            }*/



            foreach($list as $produ){
                DB::insert('insert into productos_pac (codigo,barcode,descripcion,marca_id,marca) values (?,?,?,?,?)
                            ON CONFLICT(codigo)
                            DO UPDATE SET
                            descripcion = EXCLUDED.descripcion,
                            marca_id = EXCLUDED.marca_id,
                            marca = EXCLUDED.marca;',[
                            $produ->codigo,
                            $produ->codigo,
                            $produ->producto,
                            $produ->marca_id,
                            $produ->marca,
                            ]);
            }

            /*
            $chunks=array_chunk($detalle,75);
            foreach($chunks as $record){
                DB::table('presupuesto_productos')->insert($record);
            }
            */

            $sql="SELECT coalesce(b.id,0) as id,
                    coalesce(anio,0) as anio,
					pro.codigo,
                    pro.descripcion,
                    pro.marca_id,
                    pro.marca,
                    coalesce(cod_cliente,'0') as cod_cliente,
                    coalesce(cliente,'0') as cliente,

					coalesce(precio,0) as precio,
                    coalesce(enero,0) as enero,
					coalesce(febrero,0) as febrero,
					coalesce(marzo,0) as marzo,
					coalesce(abril,0) as abril,
					coalesce(mayo,0) as mayo,
					coalesce(junio,0) as junio,
					coalesce(julio,0) as julio,
                    coalesce(agosto,0) as agosto,
					coalesce(septiembre,0) as septiembre,
					coalesce(octubre,0) as octubre,
					coalesce(noviembre,0) as noviembre,
					coalesce(diciembre,0) as diciembre,
                    coalesce(total,0) as total,
					coalesce(total_usd,0) as total_usd
                FROM productos_pac pro
                left join
                (
                    select pres.id, anio,cod_cliente,cliente,pres.codigo,precio,enero, febrero, marzo, abril, mayo, junio, julio,
                                    agosto, septiembre, octubre, noviembre, diciembre,
                                    total, total_usd,vendedor_id
                    from presupuesto_productos pres WHERE anio=?
                        and case when '0'=? then true else pres.vendedor_id=? end
                        and case when '0'=? then true else pres.marca_id=? end
                        and case when '0'=? then true else pres.cod_cliente=? end
                ) as  b on pro.codigo=b.codigo
                ORDER BY b.vendedor_id,b.cliente,pro.marca_id,pro.descripcion";

            $list = DB::select($sql,[$anio,$vendedor,$vendedor,$marca,$marca,$cliente,$cliente]);

            return $this->getOk($list);
        } catch (\Exception $e) {
                DB::rollBack();
                return $this->insertErrCustom('Error', $e->getMessage());
        }
    }


    public function presupuestoCreate(Request $request)
    {
        try {
            $detalle = $request->all();
            DB::beginTransaction();
            $anio=$detalle[0]['anio'];
            foreach($detalle as $produ){
               DB::table('presupuesto_productos')->updateOrInsert(
                    ['anio' => $produ['anio'],
                    'codigo' => $produ['codigo'],
                    'marca_id' => $produ['marca_id'],
                    'vendedor_id' => $produ['vendedor_id'],
                    'cod_cliente' => $produ['cod_cliente'],
                ],
                    $produ);
            }

            $sql="";

            DB::commit();
            return $this->insertOk('Guardado correctamente');

        } catch (\Exception $e) {
                DB::rollBack();
                return $this->insertErrCustom('Error', $e->getMessage());
        }

    }

    public function presupuestoCreateAnte(Request $request)
    {
        try {
            $detalle = $request->all();
            DB::beginTransaction();
            $anio=$detalle[0]['anio'];
            $vendedor=$detalle[0]['vendedor_id'];
            DB::delete('DELETE from presupuesto_productos where anio=? and vendedor_id=?',[$anio,$vendedor]);

            $chunks=array_chunk($detalle,75);
            foreach($chunks as $record){
                DB::table('presupuesto_productos')->insert($record);
            }
            DB::commit();
            return $this->insertOk('Guardado correctamente');

        } catch (\Exception $e) {
                DB::rollBack();
                return $this->insertErrCustom('Error', $e->getMessage());
        }

    }


public function getPessupuestoComparaProducto(Request $request)
    {
        try {
            $input = $request->all();

            $anio=$input['anio'];
            $marca=$input['marca_id'];
            $vendedor=$input['vendedor_id'];
            $cliente=$input['cliente_id'];


            $sql="select pres.codigo,
                        pres.descripcion,
                        round(avg(precio),2) as precio,
                        sum(enero) as enero,
                        sum(febrero) as febrero,
                        sum(marzo) as marzo,
                        sum(abril) as abril,
                        sum(mayo) as mayo,
                        sum(junio) as junio,
                        sum(julio) as julio,
                        sum(agosto) as agosto,
                        sum(septiembre) as septiembre,
                        sum(octubre) as octubre,
                        sum(noviembre) as noviembre,
                        sum(diciembre) as diciembre,
                        sum(total) as total,
                        round((avg(precio) *sum(total)),2) as total_usd

                from presupuesto_productos pres
                WHERE anio=?
                        and case when '0'=? then true else pres.vendedor_id=? end
                        and case when '0'=? then true else pres.marca_id=? end
                        and case when '0'=? then true else pres.cod_cliente=? end
                    group by codigo,descripcion
                    order by codigo";

            $list = DB::select($sql,[$anio,$vendedor,$vendedor,$marca,$marca,$cliente,$cliente]);

            return $this->getOk($list);
        } catch (\Exception $e) {
                DB::rollBack();
                return $this->insertErrCustom('Error', $e->getMessage());
        }
    }

    public function getPessupuestoComparaCliente(Request $request)
    {
        try {
            $input = $request->all();

            $anio=$input['anio'];
            $marca=$input['marca_id'];
            $vendedor=$input['vendedor_id'];
            $cliente=$input['cliente_id'];


            $sql="select pres.cod_cliente as codigo,
                        pres.cliente as descripcion,
                        round(avg(precio),2) as precio,
                        sum(enero) as enero,
                        sum(febrero) as febrero,
                        sum(marzo) as marzo,
                        sum(abril) as abril,
                        sum(mayo) as mayo,
                        sum(junio) as junio,
                        sum(julio) as julio,
                        sum(agosto) as agosto,
                        sum(septiembre) as septiembre,
                        sum(octubre) as octubre,
                        sum(noviembre) as noviembre,
                        sum(diciembre) as diciembre,
                        sum(total) as total,
                        round((avg(precio) *sum(total)),2) as total_usd

                from presupuesto_productos pres
                WHERE anio=?
                        and case when '0'=? then true else pres.vendedor_id=? end
                        and case when '0'=? then true else pres.marca_id=? end
                        and case when '0'=? then true else pres.cod_cliente=? end
                    group by cod_cliente,cliente
                    order by cliente";

            $list = DB::select($sql,[$anio,$vendedor,$vendedor,$marca,$marca,$cliente,$cliente]);

            return $this->getOk($list);
        } catch (\Exception $e) {
                DB::rollBack();
                return $this->insertErrCustom('Error', $e->getMessage());
        }
    }


    public function getPessupuestoComparaMarca(Request $request)
    {
        try {
            $input = $request->all();

            $anio=$input['anio'];
            $marca=$input['marca_id'];
            $vendedor=$input['vendedor_id'];
            $cliente=$input['cliente_id'];


            $sql="select pres.marca_id as codigo,
                        pres.marca as descripcion,
                        round(avg(precio),2) as precio,
                        sum(enero) as enero,
                        sum(febrero) as febrero,
                        sum(marzo) as marzo,
                        sum(abril) as abril,
                        sum(mayo) as mayo,
                        sum(junio) as junio,
                        sum(julio) as julio,
                        sum(agosto) as agosto,
                        sum(septiembre) as septiembre,
                        sum(octubre) as octubre,
                        sum(noviembre) as noviembre,
                        sum(diciembre) as diciembre,
                        sum(total) as total,
                        round((avg(precio) *sum(total)),2) as total_usd

                from presupuesto_productos pres
                WHERE anio=?
                        and case when '0'=? then true else pres.vendedor_id=? end
                        and case when '0'=? then true else pres.marca_id=? end
                        and case when '0'=? then true else pres.cod_cliente=? end
                    group by marca_id,marca
                    order by marca";

            $list = DB::select($sql,[$anio,$vendedor,$vendedor,$marca,$marca,$cliente,$cliente]);

            return $this->getOk($list);
        } catch (\Exception $e) {
                DB::rollBack();
                return $this->insertErrCustom('Error', $e->getMessage());
        }
    }

    public function getPessupuestoComparaVendedor(Request $request)
    {
        try {
            $input = $request->all();

            $anio=$input['anio'];
            $marca=$input['marca_id'];
            $vendedor=$input['vendedor_id'];
            $cliente=$input['cliente_id'];


            $sql="select pres.vendedor_id codigo,
                        round(avg(precio),2) as precio,
                        sum(enero) as enero,
                        sum(febrero) as febrero,
                        sum(marzo) as marzo,
                        sum(abril) as abril,
                        sum(mayo) as mayo,
                        sum(junio) as junio,
                        sum(julio) as julio,
                        sum(agosto) as agosto,
                        sum(septiembre) as septiembre,
                        sum(octubre) as octubre,
                        sum(noviembre) as noviembre,
                        sum(diciembre) as diciembre,
                        sum(total) as total,
                        round((avg(precio) *sum(total)),2) as total_usd

                from presupuesto_productos pres
                WHERE anio=?
                        and case when '0'=? then true else pres.vendedor_id=? end
                        and case when '0'=? then true else pres.marca_id=? end
                        and case when '0'=? then true else pres.cod_cliente=? end
                    group by vendedor_id
                    order by vendedor_id";

            $list = DB::select($sql,[$anio,$vendedor,$vendedor,$marca,$marca,$cliente,$cliente]);

            return $this->getOk($list);
        } catch (\Exception $e) {
                DB::rollBack();
                return $this->insertErrCustom('Error', $e->getMessage());
        }
    }




}
