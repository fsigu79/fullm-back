<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatResponseTrait;
use App\Models\SeriePac;
use App\Models\FacturaRpa;
use App\Models\DetalleFacturaEtiquetas;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\SqlModel;
use PDF;


use Illuminate\Http\Request;

class SeriesDespachadasController extends Controller
{
    use FormatResponseTrait;

    private $bodegasPac = [
                        // [nombre_base_datos, nombre_amigable,bodegaproductos]
                        ["fullmgyeassem", "GYE ASSEMBLY","fullmgyeassem"],
                        ["fullmgye1",     "GYE 1","fullmgye1"],
                        ["fullmgye10",    "GYE 2","fullmgye10"],
                        ["fullmgye3",     "GYE 3","fullmgye3"],
                        ["fullmuio1",     "UIO","fullmuio1"],
                        ["fullm",         "CUENCA","fullm"],
                        ["fullmgyeassem2","GYE ASSEMBLY 2","fullmgyeassem2"],
                        ["fullmcuenca1","CON BOD VIRTUAL","fullmgyeassem"],
                        ["fullmconsigvirt","CON PISO VENTAS","fullmgyeassem"]
                    ];

    private $sqlgen="SELECT 'xbodega' as bodega1,
                            HS.serie05,
                            HS.fecmov05,
                            FV.nomcte31,
                            HS.nocomp05,
                             CASE LEFT(HS.nocomp05, 7)
                                WHEN '001-103' THEN 'GYE ASSEMBLY'
                                WHEN '001-107' THEN 'GYE 2'
                                WHEN '001-100' THEN 'CUENCA'
                                WHEN '001-104' THEN 'UIO'
                                WHEN '001-102' THEN 'GYE 2'
                                WHEN '001-117' THEN 'GYE ASSEMBLY 2'
                                WHEN '001-114' THEN 'GYE 3'
                                WHEN '001-116' THEN 'GYE 4'
                                ELSE ''
                            END AS bodega05,
                            HS.codprod05,
                            MP.desprod01,
                            SR.chasis04,
                            SR.color04,
                            SR.anio04,
                            SR.coddest04,
                            FV.prepago,
                            FV.vtabta31,
                            FV.descto31,
                            FV.itm31 AS iva,
                            AVG(MC.totdoc43) AS total_factura,
                            SUM(MC.saldoregmov43) AS saldo_total_factura,
                            (AVG(MC.totdoc43) - SUM(MC.saldoregmov43)) AS total_pagado,
                            CASE
                                WHEN (AVG(MC.totdoc43) - SUM(MC.saldoregmov43)) >= (AVG(MC.totdoc43) * 0.8) THEN 'Pagada'
                                ELSE 'Pendiente'
                            END AS estado_pago
                        FROM xbase.hisser HS
                        INNER JOIN xfacturas.maepro MP ON HS.codprod05 = MP.codprod01
                        INNER JOIN xbase.maeser SR ON HS.serie05 = SR.serie04
                        LEFT JOIN xfacturas.maefac FV ON HS.nocomp05 = FV.nofact31
                        LEFT JOIN fullm.movcte MC ON HS.nocomp05 = MC.numdoc43 AND MC.tipodoc43 = '02'
                        WHERE
                            HS.fecmov05 >= 'xfinicio' and HS.fecmov05 <= 'xffin'
                            AND HS.tipotra05 = '80'
                            AND HS.codprod05 LIKE 'ASM-%'
                        GROUP BY
                            HS.serie05,
                            HS.fecmov05,
                            FV.nomcte31,
                            HS.nocomp05,
                            HS.codprod05,
                            MP.desprod01,
                            SR.chasis04,
                            SR.color04,
                            SR.anio04,
                            SR.coddest04,
                            FV.prepago,
                            FV.vtabta31,
                            FV.descto31,
                            FV.itm31 ";

    public function obtenerSeries(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';
        $sql='';
        $anio=$request['anio'];

        try{
            $query="SELECT HS.serie05, HS.fecmov05, FV.nomcte31, HS.nocomp05, HS.codprod05, MP.desprod01, SR.chasis04, SR.color04, SR.anio04, SR.coddest04,
                    FV.prepago, MC.totdoc43, MC.saldoregmov43,FV.vtabta31,FV.descto31,FV.itm31 as iva
                    FROM xbase.hisser HS
                    INNER JOIN xbase.maepro MP ON HS.codprod05 = MP.codprod01
                    INNER JOIN xbase.maeser SR ON HS.serie05 = SR.serie04
                    LEFT JOIN xbase.maefac FV ON HS.nocomp05 = FV.nofact31
                    LEFT JOIN fullm.movcte MC ON HS.nocomp05 = MC.numdoc43 AND MC.tipodoc43 = '80'
                    WHERE HS.fecmov05 >= 'xfinicio'  and HS.fecmov05 <= 'xffin'
                        AND HS.tipotra05 = '80'
                        AND HS.codprod05 LIKE 'ASM-%'";


            // Crear un array para las bodegas
              $bodegas = [
                        "jcevgyeassem",
                        "jcevgye1",
                        "jcevgye10",
                        "jcevgye3",
                        "jcevgye4",
                        "jcevuio1",
                        "jcev",
                        "jcevgyeassem2",
                        "Jcevcuenca1",
                        "jcevconsigvirt"
                    ];

            // Construir la consulta final dependiendo de las empresas activas
            $finalQuery = '';
            foreach ($bodegas as $index => $bodega) {
                $tempQuery = str_replace('xbase', $bodega, $query);
                $tempQuery = str_replace('xfinicio', $inicio, $tempQuery);
                $tempQuery = str_replace('xffin', $fin, $tempQuery);

                if ($index > 0) {
                    $finalQuery .= ' union all ';
                }

                $finalQuery .= $tempQuery;
            }

            $finalQuery .= " order by serie05";

            $list = DB::connection('mysqlpac')->select($finalQuery);

            return $this->getOk($list);
         }catch (\Exception $e) {
            return $this->insertErrCustom($input, $e->getMessage());
        }
    }




    public function seriesDespachadas(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $fin=$request['ffin'].' 23:59:00';

        try{
            // Crear un array para las bodegas
                    $bodegas = [
                        // [nombre_base_datos, nombre_amigable,bodegaproductos]
                        ["fullmgyeassem", "GYE ASSEMBLY","fullmgyeassem"],
                        ["fullmgye1",     "GYE 1","fullmgye1"],
                        ["fullmgye10",    "GYE 2","fullmgye10"],
                        ["fullmgye3",     "GYE 3","fullmgye3"],
                        ["fullmuio1",     "UIO","fullmuio1"],
                        ["fullm",         "CUENCA","fullm"],
                        ["fullmgyeassem2","GYE ASSEMBLY 2","fullmgyeassem2"],
                        ["fullmcuenca1","CON BOD VIRTUAL","fullmgyeassem"],
                        ["fullmconsigvirt","CON PISO VENTAS","fullmgyeassem"]
                    ];


            // Construir la consulta final dependiendo de las empresas activas
            $finalQuery = '';
            foreach ($bodegas as $index => $bodega) {
                $tempQuery = str_replace('xbase', $bodega[0], $this->sqlgen);
                $tempQuery = str_replace('xbodega', $bodega[1], $tempQuery);
                $tempQuery = str_replace('xfacturas', $bodega[2], $tempQuery);
                $tempQuery = str_replace('xfinicio', $inicio, $tempQuery);
                $tempQuery = str_replace('xffin', $fin, $tempQuery);

                if ($index > 0) {
                    $finalQuery .= ' union all ';
                }

                $finalQuery .= $tempQuery;
            }

            $finalQuery .= " order by serie05";

              //fsigu sqls

         $box = new SqlModel();
            $box->sql= $finalQuery;
            $box->sql1=$finalQuery;
            $box->save();

            $listSeries = DB::connection('mysqlpac')->select($finalQuery);

            $sqlGuias="select g.id as guia_id,fecha_inicio,
                                g.serie||'-'||lpad(g.numero,9,'0') as numero_guia,
                                g.ruc,
                                cliente
                                direccion,
                                origen,
                                transportista_id,
                                trim(documentos)::varchar as factura,
                                nombres as transportista,
								d.codigo,
								d.serie
                        from guias_remision g
						inner join guias_remisiond d on g.id=d.guiar_id
                        inner join transportistas t on t.id = g.transportista_id
                        where fecha_inicio>=? and fecha_inicio<=?
							and d.codigo like 'ASM-%' ";
            $listGuias = DB::select($sqlGuias,[$inicio,$fin]);

            //Convertimos las listas a colecciones
            $series = collect($listSeries);
            $guias  = collect($listGuias);

            // 1) Índice single-pass de guías por clave compuesta "CODIGO|SERIE", guardando la MÁS RECIENTE
            $guiaIndex = $guias->reduce(function (array $carry, $g) {
                $codigo = strtoupper(trim($g->codigo ?? ''));
                $serie  = strtoupper(trim($g->serie  ?? ''));
                if ($codigo === '' || $serie === '') {
                    return $carry;
                }
                $key = $codigo.'|'.$serie;

                // Comparar fecha_inicio y quedarnos con la más reciente
                if (!isset($carry[$key])) {
                    $carry[$key] = $g;
                } else {
                    // Si son strings ISO/‘Y-m-d H:i:s’, comparar directo; si no, usa strtotime/Carbon
                    $carry[$key] = ($g->fecha_inicio > $carry[$key]->fecha_inicio) ? $g : $carry[$key];
                }
                return $carry;
            }, []);

            // 2) Mapear series y anexar datos si existe coincidencia
            $resultado = $series->map(function ($s) use ($guiaIndex) {
                $key = strtoupper(trim($s->codprod05 ?? '')).'|'.strtoupper(trim($s->serie05 ?? ''));

                $s->numero_guia      = null;
                $s->transportista    = null;
                $s->fecha_inicio     = null;
                $s->estado_despacho  = 'NO DESPACHADA';

                if (isset($guiaIndex[$key])) {
                    $g = $guiaIndex[$key];
                    $s->numero_guia      = $g->numero_guia ?? null;
                    $s->transportista    = $g->transportista ?? null;
                    $s->fecha_inicio     = $g->fecha_inicio ?? null;
                    $s->estado_despacho  = 'DESPACHADA';
                }

                return $s;
            });

            // Si quieres array limpio para JSON:
            return $this->getOk($resultado->values());

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


    public function seriesBuscar(Request $request)
    {
        $input = $request->all();
        $inicio=$request['finicio'].' 00:00:00';
        $serie=$request['serie'];
        $fin=$request['ffin'].' 23:59:00';

        try{
            // Construir la consulta final dependiendo de las empresas activas
            $query="SELECT 'xbodega' as bodega1,
                            HS.serie05,
                            HS.tipotra05,
                            HS.fecmov05,
                            HS.nocomp05,
                            HS.codprod05,
                            MP.desprod01,
                            SR.chasis04,
                            SR.color04,
                            SR.anio04,
                            SR.coddest04
                        FROM
                            xbase.hisser HS
                        INNER JOIN
                            xproductos.maepro MP ON HS.codprod05 = MP.codprod01
                        INNER JOIN
                            xbase.maeser SR ON HS.serie05 = SR.serie04
                        WHERE
                            HS.serie05='xserie' ";

            $finalQuery = '';
            foreach ($this->bodegasPac as $index => $bodega) {
                $tempQuery = str_replace('xbase', $bodega[0], $query);
                $tempQuery = str_replace('xbodega', $bodega[1], $tempQuery);
                $tempQuery = str_replace('xproductos', $bodega[2], $tempQuery);
                $tempQuery = str_replace('xserie', $serie, $tempQuery);
                if ($index > 0) {
                    $finalQuery .= ' union all ';
                }

                $finalQuery .= $tempQuery;
            }
            $finalQuery .= " order by fecmov05 desc";

            $listSeries = DB::connection('mysqlpac')->select($finalQuery);

            // Si quieres array limpio para JSON:
            return $this->getOk($listSeries);

        }catch (\Exception $e) {
            return $this->insertErrCustom($input, $e->getMessage());
        }

    }



}
