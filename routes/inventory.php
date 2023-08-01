<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LineaController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DestinoController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\InventarioTransitoController;
use App\Http\Controllers\PuertosEmbarqueController;
use App\Http\Controllers\SiniestroController;

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



});


Route::group([
    'prefix' => 'product',
], function () {
    Route::get('all', [ProductController::class, 'all']);
    Route::get('list', [ProductController::class, 'list']);
    Route::get('search', [ProductController::class, 'searchProducts']);
    Route::get('productId/{id}', [ProductController::class, 'productId']);
    Route::post('create', [ProductController::class, 'create']);
    Route::put('update/{id}', [ProductController::class, 'update']);
    Route::delete('delete/{id}', [ProductController::class, 'delete']);
    Route::get('getImage/{filename}', [ProductController::class, 'getImage']);
    Route::post('addImage', [ProductController::class, 'addImage']);
});

Route::group([
    'prefix' => 'linea',
], function () {
    Route::get('all', [LineaController::class, 'all']);
    Route::get('list', [LineaController::class, 'list']);
    Route::get('search', [LineaController::class, 'search']);
    Route::get('byId/{id}', [LineaController::class, 'byId']);
    Route::post('create', [LineaController::class, 'create']);
    Route::put('update/{id}', [LineaController::class, 'update']);
    Route::delete('delete/{id}', [LineaController::class, 'delete']);
});

Route::group([
    'prefix' => 'grupo',
], function () {
    Route::get('all', [GrupoController::class, 'all']);
    Route::get('list', [GrupoController::class, 'list']);
    Route::get('grupoId/{id}', [GrupoController::class, 'grupoId']);
    Route::post('create', [GrupoController::class, 'create']);
    Route::put('update/{id}', [GrupoController::class, 'update']);
    Route::delete('delete/{id}', [GrupoController::class, 'delete']);
});


Route::group([
    'prefix' => 'destino',
], function () {
    Route::get('list', [DestinoController::class, 'list']);
    Route::get('list/{id}', [DestinoController::class, 'getData']);
    Route::post('create', [DestinoController::class, 'create']);
    Route::put('update/{id}', [DestinoController::class, 'update']);
    Route::delete('delete/{id}', [DestinoController::class, 'delete']);
    Route::get('listnum', [DestinoController::class, 'listNumeros']);
    Route::get('listnumsin', [DestinoController::class, 'listNumerosSiniestros']);
});


Route::group([
    'prefix' => 'ing',
], function () {
    Route::get('list', [MovimientoInventarioController::class, 'list']);
    Route::get('list/{id}', [MovimientoInventarioController::class, 'findById']);
    Route::post('create', [MovimientoInventarioController::class, 'save']);
    Route::put('update/{id}', [MovimientoInventarioController::class, 'save']);
    Route::delete('delete/{id}', [MovimientoInventarioController::class, 'delete']);
});


Route::group([
    'prefix' => 'egr',
], function () {
    Route::get('list', [MovimientoInventarioController::class, 'list']);
    Route::get('list/{id}', [MovimientoInventarioController::class, 'findById']);
    Route::post('create', [MovimientoInventarioController::class, 'save']);
    //Route::put('update/{id}', [MovimientoInventarioController::class, 'updatePac']);
    Route::post('update/{id}', [MovimientoInventarioController::class, 'save']);
    Route::post('delete/{id}', [MovimientoInventarioController::class, 'save']);
    //Route::delete('delete/{id}', [MovimientoInventarioController::class, 'deleteEgreso']);
    Route::put('approve', [MovimientoInventarioController::class, 'updateAprobar']);
    Route::put('register', [MovimientoInventarioController::class, 'updateRegistrado']);
    Route::put('negar', [MovimientoInventarioController::class, 'updateNegado']);
    Route::get('movdetalle', [MovimientoInventarioController::class, 'detalleMovimientos']);
});



Route::group([
    'prefix' => 'transito',
], function () {
    Route::get('list', [InventarioTransitoController::class, 'list']);
    Route::get('list/{id}', [InventarioTransitoController::class, 'findById']);
    Route::post('create', [InventarioTransitoController::class, 'save']);
    Route::post('update/{id}', [InventarioTransitoController::class, 'save']);
    Route::post('delete/{id}', [InventarioTransitoController::class, 'save']);
    Route::put('liquidado', [InventarioTransitoController::class, 'updateLiquidado']);

});



Route::group([
    'prefix' => 'puerto',
], function () {
    Route::get('list', [PuertosEmbarqueController::class, 'list']);
    Route::get('listact', [PuertosEmbarqueController::class, 'listActivos']);
    Route::get('list/{id}', [PuertosEmbarqueController::class, 'findById']);
    Route::post('create', [PuertosEmbarqueController::class, 'create']);
    Route::put('update/{id}', [PuertosEmbarqueController::class, 'update']);
    Route::delete('delete/{id}', [PuertosEmbarqueController::class, 'delete']);


});


Route::group([
    'prefix' => 'sin',
], function () {
    Route::get('list', [SiniestroController::class, 'list']);
    Route::get('list/{id}', [SiniestroController::class, 'findById']);
    Route::post('create', [SiniestroController::class, 'save']);
    //Route::put('update/{id}', [MovimientoInventarioController::class, 'updatePac']);
    Route::post('update/{id}', [SiniestroController::class, 'save']);
    Route::post('delete/{id}', [SiniestroController::class, 'save']);
    //Route::delete('delete/{id}', [MovimientoInventarioController::class, 'deleteEgreso']);
    Route::put('approve', [SiniestroController::class, 'updateAprobar']);
    Route::put('register', [SiniestroController::class, 'updateRegistrado']);
    Route::put('facturado', [SiniestroController::class, 'updateFacturado']);
    Route::put('negar', [SiniestroController::class, 'updateNegado']);
});
