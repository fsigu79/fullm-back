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

           //--productos menos los de categoria I=importaciones,G=gastos,9=servicios
            $query=  "select codprod01 as codigo,desprod01 as producto,marca01 as marca_id,
                        (SELECT DISTINCT nomtab FROM jcev.maetab WHERE numtab='4530' AND codtab<>'' AND codtab=jcev.maepro.marca01) AS marca
                    from jcev.maepro where tipprod01='S' AND statuspro01='S' and catprod01 not in('I','G','9') order by desprod01";
            $list = DB::connection('mysqlpac')->select($query);

            //DB::beginTransaction();
            DB::delete('DELETE from productos_pac');

            foreach($list as $produ){
                DB::insert('insert into productos_pac (codigo,barcode,descripcion,marca_id,marca) values (?,?,?,?,?)',[
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

            $sql="SELECT coalesce(b.id,0) as id, coalesce(anio,0) as anio,
					pro.codigo,pro.descripcion, pro.marca_id, pro.marca,
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
                    select pres.id, anio,pres.codigo,precio,enero, febrero, marzo, abril, mayo, junio, julio,
                                    agosto, septiembre, octubre, noviembre, diciembre,
                                    total, total_usd
                    from presupuesto_productos pres WHERE anio=? and vendedor_id=?
                ) as  b on pro.codigo=b.codigo

                where case when '0'=? then true else pro.marca_id=? end
                ORDER BY pro.marca_id,pro.descripcion";

            $list = DB::select($sql,[$anio,$vendedor,$marca,$marca]);

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




}
