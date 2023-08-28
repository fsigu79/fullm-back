<?php

namespace App\Http\Controllers;

use App\Http\Traits\FormatResponseTrait;
use App\Models\Country;
use App\Models\Provincia;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogoController extends Controller
{
     use FormatResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function listProvincias()
    {
        $query=  "select codtab as codigo,nomtab as provincia from jcev.maetab where numtab='33' AND codtab <> ''";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

    public function listProvinciasTodoMoto()
    {
        $query=  "select codtab as codigo,nomtab as provincia from todomoto.maetab where numtab='33' AND codtab <> ''";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

    public function listProvinciasCredimport()
    {
        $query=  "select codtab as codigo,nomtab as provincia from vintipart.maetab where numtab='33' AND codtab <> ''";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

    public function listVendedores()
    {
        $query=  "SELECT codtab as codigo,nomtab as vendedor FROM jcev.maetab WHERE numtab='73' AND codtab <> ''";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

    public function listVendedoresTodoMoto()
    {
        $query=  "SELECT codtab as codigo,nomtab as vendedor FROM todomoto.maetab WHERE numtab='73' AND codtab <> ''";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

    public function listVendedoresCredimport()
    {
        $query=  "SELECT codtab as codigo,nomtab as vendedor FROM vintipart.maetab WHERE numtab='73' AND codtab <> ''";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

    public function listCatClientes()
    {
        $query=  "SELECT codcate as codigo,desccate as catcliente FROM jcev.categorias WHERE tipocate='03'";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

     public function listCatClientesTodoMoto()
    {
        $query=  "SELECT codcate as codigo,desccate as catcliente FROM todomoto.categorias WHERE tipocate='03'";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

     public function listCatClientesCredimport()
    {
        $query=  "SELECT codcate as codigo,desccate as catcliente FROM vintipart.categorias WHERE tipocate='03'";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

    public function listCatProductos()
    {
        $query=  "SELECT codcate as codigo,desccate as codproducto FROM jcev.categorias WHERE tipocate='02' ";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

    public function listCatProductosTodoMoto()
    {
        $query=  "SELECT codcate as codigo,desccate as codproducto FROM todomoto.categorias WHERE tipocate='02' ";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

    public function listCatProductosCredimport()
    {
        $query=  "SELECT codcate as codigo,desccate as codproducto FROM vintipart.categorias WHERE tipocate='02' ";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

    public function listMarcas()
    {
        $query=  "SELECT DISTINCT codtab as codigo,nomtab as marca FROM jcev.maetab WHERE numtab = '4530' AND codtab <> '' order by nomtab";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

    public function listMarcasTodoMoto()
    {
        $query=  "SELECT DISTINCT codtab as codigo,nomtab as marca FROM todomoto.maetab WHERE numtab = '4530' AND codtab <> '' order by nomtab";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

    public function listMarcasCredimport()
    {
        $query=  "SELECT DISTINCT codtab as codigo,nomtab as marca FROM vintipart.maetab WHERE numtab = '4530' AND codtab <> '' order by nomtab";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }


     public function listClientes()
    {
        $query=  "select codcte01 as codigo,nomcte01 as cliente from jcev.maecte order by nomcte01";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }


    public function listClientesTodoMoto()
    {
        $query=  "select codcte01 as codigo,nomcte01 as cliente from todomoto.maecte order by nomcte01";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

    public function listClientesCredimport()
    {
        $query=  "select codcte01 as codigo,nomcte01 as cliente from vintipart.maecte order by nomcte01";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }



    public function listProductos()
    {
        //--productos menos los de categoria I=importaciones,G=gastos,9=servicios
        $query=  "select codprod01 as codigo,desprod01 as producto from jcev.maepro where tipprod01='S' AND statuspro01='S' and catprod01 not in('I','G','9') order by desprod01";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

    public function listProductosTodoMoto()
    {
        //--productos menos los de categoria I=importaciones,G=gastos,9=servicios
        $query=  "select codprod01 as codigo,desprod01 as producto from todomoto.maepro where tipprod01='S' AND statuspro01='S' and catprod01 not in('I','G','9') order by desprod01";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

    public function listProductosCredimport()
    {
        //--productos menos los de categoria I=importaciones,G=gastos,9=servicios
        $query=  "select codprod01 as codigo,desprod01 as producto from vintipart.maepro where tipprod01='S' AND statuspro01='S' and catprod01 not in('I','G','9') order by desprod01";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

    public function listProductosCodigo()
    {
        //--productos menos los de categoria I=importaciones,G=gastos,9=servicios
        $query=  "select codprod01 as codigo,concat(codprod01,'-',desprod01) as producto,desprod01 from jcev.maepro where tipprod01='S' AND statuspro01='S' and catprod01 not in('I','G','9') order by desprod01";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

    public function listProductosCodigoTodoMoto()
    {
        //--productos menos los de categoria I=importaciones,G=gastos,9=servicios
        $query=  "select codprod01 as codigo,concat(codprod01,'-',desprod01) as producto from todomoto.maepro where tipprod01='S' AND statuspro01='S' and catprod01 not in('I','G','9') order by desprod01";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }

    public function listProductosCodigoCredimport()
    {
        //--productos menos los de categoria I=importaciones,G=gastos,9=servicios
        $query=  "select codprod01 as codigo,concat(codprod01,'-',desprod01) as producto from vintipart.maepro where tipprod01='S' AND statuspro01='S' and catprod01 not in('I','G','9') order by desprod01";
        //$list = DB::select($sql,[$request['cliente_id']]);
        $list = DB::connection('mysqlpac')->select($query);

        return $this->getOk($list);
    }


}
