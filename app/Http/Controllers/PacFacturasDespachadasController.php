<?php

namespace App\Http\Controllers;

use App\Http\Traits\FormatResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use DateTime;


class PacFacturasDespachadasController extends Controller
{
    use FormatResponseTrait;

    private $sqlgen="SELECT DISTINCT
                        'FULLM' as Empresa,
                        mid(nofact31,1,7) AS agencia,
                        'FA' as tipo,
                        nofact31 AS numero_factura,
                        nocte31 AS codigo_cliente,
                        fecfact31 AS fecha,
                        ruc31 as ruc,
                        vendedor.nomtab AS vendedor,
                        nomcte01 as cliente
                    FROM xbodega.maefac
                    left join xmatriz.movcte as cliente on cliente.tipodoc43 = '02' and cliente.numdoc43 = maefac.nofact31
                    left JOIN xbodega.maetab AS condicionPago	ON condicionPago.numtab = '72' AND condicionPago.codtab =  trim(maefac.condpag31)
                    left JOIN xbodega.maetab AS vendedor ON vendedor.numtab = '73' AND vendedor.codtab =  maefac.novend31
                    left JOIN xmatriz.maecte AS clien ON clien.codcte01 = maefac.nocte31
                    where cvanulado31 != '9' and fecfact31>='xfinicio'and fecfact31<='xffin'";

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function facturasDespachadas(Request $request)
    {
        $input = $request->all();
        $finicio=$request['finicio'];
        $ffin=$request['ffin'];

        try{
            //serie 001-107
            $sqlFacturas=$this->generaQuery('fullm','fullmgye10',$finicio,$ffin);
            //serie 001-103 guayaquil assembly
            $sqlFacturas=$sqlFacturas.' UNION ALL '.$this->generaQuery('fullm','fullmgyeassem',$finicio,$ffin);
            //serie 001-117 guayaquil assembly 2
            $sqlFacturas=$sqlFacturas.' UNION ALL '.$this->generaQuery('fullm','fullmgyeassem2',$finicio,$ffin);


            $listFacturas = DB::connection('mysqlpac')->select($sqlFacturas,[]);

            $sqlGuias="select g.id as guia_id,
                                serie||'-'||lpad(numero,9,'0') as numero_guia,
                                g.ruc,
                                cliente
                                direccion,
                                origen,
                                transportista_id,
                                trim(documentos)::varchar as factura,
                                nombres as transportista
                        from guias_remision g
                        inner join transportistas t on t.id = g.transportista_id
                        where fecha_inicio>=? AND fecha_inicio<=?";
            $listGuias = DB::select($sqlGuias,[$finicio,$ffin]);

            //Convertimos las listas a colecciones
            $facturas = collect($listFacturas);
            $guias = collect($listGuias);

            //return $this->getOk($guias);

            //Relacionar las listas basándose en numero_factura y factura
           //Relacionar las listas basándose en numero_factura y factura
           $facturas = $facturas->map(function ($factura) use ($guias) {
                // Asegurar que 'numero_factura' no tenga espacios extras
                $numero_factura = trim($factura->numero_factura);

                // Buscar TODAS las guías donde la factura esté incluida en el campo 'factura' (puede tener varias separadas por comas)
                $guiaEncontradas = $guias->filter(function ($guia) use ($numero_factura) {
                    // Convertir el campo 'factura' en un array dividiendo por coma
                    $facturasEnGuia = array_map('trim', explode(',', $guia->factura));

                    // Verificar si $numero_factura está en la lista de facturas de esta guía
                    return in_array($numero_factura, $facturasEnGuia);
                });

                // Obtener todos los números de guía como un string separado por comas
                //$numeros_guia = $guiaEncontradas->pluck('numero_guia')->implode(', ');
                 // Obtener todos los 'numero_guia' de las guías donde se encontró la factura
                $numeros_guia = $guiaEncontradas->pluck('numero_guia')->unique()->implode(', ');

                // Agregar los campos adicionales
                $factura->despachado = $guiaEncontradas->isNotEmpty() ? 'DESPACHADA' : 'NO DESPACHADA';
                $factura->numero_guia = $numeros_guia ?: '';

                return $factura;
            });
            //Convertirlo a un array si es necesario:
            //$listado_final = $facturas->toArray();
            return $this->getOk($facturas);

        }catch (\Exception $e) {
            return $this->insertErrCustom($input, $e->getMessage());
        }

    }


    public function generaQuery($matriz,$bodega,$inicio,$fin)
    {
        $query=  str_replace('xmatriz',$matriz,$this->sqlgen);
        $query=  str_replace('xbodega',$bodega,$query);
        $query=  str_replace('xfinicio',$inicio,$query);
        $query=  str_replace('xffin',$fin,$query);


        return $query;
    }





}
