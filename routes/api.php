<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\PlantillaController;
use App\Http\Controllers\ClientesPacController;
use App\Http\Controllers\ProductoPacController;
use App\Http\Controllers\ConsultaExtPacController;
use App\Http\Controllers\GuiasPacController;
use App\Http\Controllers\PacPresupuestoController;
use App\Http\Controllers\PacCarteraController;
use App\Http\Controllers\PacVentasController;
use App\Http\Controllers\PacGerencialDiarioController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ModeloController;
use App\Http\Controllers\MotoController;
use App\Http\Controllers\SegmentoController;
use App\Http\Controllers\CobuController;
use App\Http\Controllers\EadeController;
use App\Http\Controllers\PacVentasComparaController;
use App\Http\Controllers\AuditoriaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');

});



Route::group([
   'prefix' => 'menu',
], function () {

   Route::get('list', [MenuController::class, 'list']);
   Route::get('list/{id}', [MenuController::class, 'findById']);
});

Route::group([
    'prefix' => 'company',
], function () {
    Route::get('lista/{id}', [CompanyController::class, 'findById']);
    Route::put('editar/{id}', [CompanyController::class, 'edit']);
});


Route::group([
        'prefix' => 'user',
    ], function () {
        Route::get('list', [UserController::class, 'all']);
        Route::get('rep', [UserController::class, 'salesrep']);
        Route::get('cobra', [UserController::class, 'cobradorrep']);
        Route::post('register', [UserController::class, 'register']);
        Route::put('edit/{id}', [UserController::class, 'update']);
        Route::get('all', [UserController::class, 'all']);
        Route::get('profile/{auid}', [UserController::class, 'getUserProfile']);
        Route::put('update', [UserController::class, 'update']);
        Route::get('data/{id}', [UserController::class, 'getData']);
        Route::get('search-users', [UserController::class, 'searchUsers']);
        Route::get('userId/{id}', [UserController::class, 'userId']);
        Route::post('upload', [UserController::class, 'upload'])->middleware(ApiAuthMiddleware::class);
        Route::get('getImage/{filename}', [UserController::class, 'getImage']);

    });



Route::group([
    'prefix' => 'account',
], function () {
    Route::get('todos', [AccountController::class, 'todos']);
});





Route::group([
    'prefix' => 'profile',
], function () {
    Route::get('list', [ProfileController::class, 'list']);
    Route::get('list/{id}', [ProfileController::class, 'findById']);
    Route::post('create', [ProfileController::class, 'create']);
    Route::put('edit/{id}', [ProfileController::class, 'edit']);
});


Route::group([
    'prefix' => 'access',
], function () {
    Route::get('program/{profile}/{program}', [ProfileController::class, 'findByProgram']);
    Route::get('menu/{userid}', [ProfileController::class, 'findByUser']);
});


Route::group([
    'prefix' => 'document',
], function () {
    Route::get('getseries/{code}/{module}', [DocumentController::class, 'getSeries']);
    Route::get('{id}', [DocumentController::class, 'getById']);
    Route::get('doc/plant', [DocumentController::class, 'getDocumentosPlantilla']);
    Route::get('doc/list', [DocumentController::class, 'list']);
    Route::get('doc/cont', [DocumentController::class, 'getDocumentosContables']);
    Route::post('create', [DocumentController::class, 'create']);
    Route::put('edit/{id}', [DocumentController::class, 'edit']);
    Route::get('list/{id}', [DocumentController::class, 'getData']);
});



Route::group([
    'prefix' => 'plantilla',
], function () {
    Route::get('lista', [PlantillaController::class, 'getDocumentosPlantilla']);
    Route::post('create', [PlantillaController::class, 'save']);
});



//
//Tutas para consultas consutas para pac
//**
Route::group([
        'prefix' => 'cat',
    ], function () {
        Route::get('prov/list', [CatalogoController::class, 'listProvincias']);
        Route::get('vend/list', [CatalogoController::class, 'listVendedores']);
        Route::get('catc/list', [CatalogoController::class, 'listCatClientes']);
        Route::get('catp/list', [CatalogoController::class, 'listCatProductos']);
        Route::get('marc/list', [CatalogoController::class, 'listMarcas']);
        Route::get('clie/list', [CatalogoController::class, 'listClientes']);
        Route::get('prod/list', [CatalogoController::class, 'listProductos']);
        Route::get('prodcod/list', [CatalogoController::class, 'listProductosCodigo']);

    });

Route::group([
        'prefix' => 'audi',
    ], function () {
       Route::get('list', [AuditoriaController::class, 'getByDocumento']);
    });




Route::group([
    'prefix' => 'pac',
], function () {
    Route::get('vtames', [ClientesPacController::class, 'ventamescliente']);
    Route::get('vtamesven', [ClientesPacController::class, 'ventamesvendedor']);
    Route::get('vtamespro', [ClientesPacController::class, 'ventamesproducto']);
    Route::get('vtamesmar', [ClientesPacController::class, 'ventamesmarca']);
    Route::get('export-label/{id}', [ProductoPacController::class, 'exportLabel']);
    Route::get('produpac', [ProductoPacController::class, 'listprodpac']);
    Route::get('prespac', [PacPresupuestoController::class, 'presupuestoPac']);
    Route::get('prespacvs', [PacPresupuestoController::class, 'presupuestoPacCompara']);
    Route::get('segucon', [PacCarteraController::class, 'seguroConfianza']);
    Route::get('vensem', [PacVentasController::class, 'ventasSemana']);
    Route::get('gerdia', [PacGerencialDiarioController::class, 'ventasmarca']);

    Route::get('vtacomcli', [PacVentasComparaController::class, 'ventamescliente']);
    Route::get('vtacomven', [PacVentasComparaController::class, 'ventamesvendedor']);
    Route::get('vtacompro', [PacVentasComparaController::class, 'ventamesproducto']);
    Route::get('vtacommar', [PacVentasComparaController::class, 'ventamesmarca']);

    Route::get('searchproddupac', [ProductoPacController::class, 'searchProductsPac']);
    Route::get('prodbyidpac/{id}', [ProductoPacController::class, 'productIdPac']);

    Route::get('searchclipac', [ClientesPacController::class, 'searchClientesPac']);
    Route::get('searchprovepac', [ClientesPacController::class, 'searchProveedorPac']);

});




Route::group([
    'prefix' => 'marca',
], function () {
    Route::get('list', [MarcaController::class, 'list']);
    Route::get('list/{id}', [MarcaController::class, 'getById']);
    Route::post('create', [MarcaController::class, 'create']);
    Route::put('edit', [MarcaController::class, 'edit']);
    Route::delete('delete/{id}', [MarcaController::class, 'delete']);
});

Route::group([
    'prefix' => 'modelo',
], function () {
    Route::get('list', [ModeloController::class, 'list']);
    Route::get('list/{id}', [ModeloController::class, 'getById']);
    Route::post('create', [ModeloController::class, 'create']);
    Route::put('edit', [ModeloController::class, 'edit']);
    Route::delete('delete/{id}', [ModeloController::class, 'delete']);
});

Route::group([
    'prefix' => 'segmento',
], function () {
    Route::get('list', [SegmentoController::class, 'list']);
    Route::get('list/{id}', [SegmentoController::class, 'getById']);
    Route::post('create', [SegmentoController::class, 'create']);
    Route::put('edit', [SegmentoController::class, 'edit']);
    Route::delete('delete/{id}', [SegmentoController::class, 'delete']);
});

Route::group([
    'prefix' => 'moto',
], function () {
    Route::get('list', [MotoController::class, 'list']);
    Route::get('list/{id}', [MotoController::class, 'getById']);
    Route::post('create', [MotoController::class, 'create']);
    Route::put('edit', [MotoController::class, 'edit']);
    Route::delete('delete/{id}', [MotoController::class, 'delete']);
});

Route::group([
    'prefix' => 'eade',
], function () {
    Route::get('list', [EadeController::class, 'list']);
    Route::post('create', [EadeController::class, 'create']);
    Route::get('search', [EadeController::class, 'searchProductsEade']);
});

Route::group([
    'prefix' => 'cobus',
], function () {
    Route::get('list', [CobuController::class, 'list']);
    Route::post('create', [CobuController::class, 'create']);
    Route::get('search', [CobuController::class, 'searchProductsCobus']);
});

Route::group([
    'prefix' => 'pacg',
], function () {
    Route::get('guiasvta', [GuiasPacController::class, 'ventaGuias']);
    Route::get('guias/det', [GuiasPacController::class, 'guiasDetalle']);
});


Route::group([
    'prefix' => 'pacext',
], function () {
    Route::get('chasis', [ConsultaExtPacController::class, 'datosPorChasis']);


});

