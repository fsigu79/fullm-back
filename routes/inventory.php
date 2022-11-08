<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LineaController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DestinoController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\KardexController;

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
});


Route::group([
    'prefix' => 'kardex',
], function () {
    Route::get('list', [KardexController::class, 'consulta_kardex']);
});
