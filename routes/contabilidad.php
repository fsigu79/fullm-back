<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CuentaController;
use App\Http\Controllers\AsientoController;
use App\Http\Controllers\BancoController;

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
    'prefix' => 'cuenta',
], function () {
    Route::get('list', [CuentaController::class, 'list']);
    Route::get('listantp', [CuentaController::class, 'listAnticipoProvedor']);
    Route::get('listantc', [CuentaController::class, 'listAnticipoCliente']);
    Route::get('search', [CuentaController::class, 'searchProducts']);
    Route::get('find/byc/{codigo}', [CuentaController::class, 'cuentaByCodigo']);
    Route::get('/{id}', [CuentaController::class, 'productId']);
    Route::post('create', [CuentaController::class, 'create']);
    Route::put('edit', [CuentaController::class, 'edit']);
    Route::delete('eliminar/{id}', [CuentaController::class, 'eliminar']);
    Route::get('search', [CuentaController::class, 'searchCuentas']);
});

Route::group([
    'prefix' => 'banco',
], function () {
    Route::get('list', [BancoController::class, 'list']);
    Route::get('/{id}', [BancoController::class, 'productId']);
    Route::post('create', [BancoController::class, 'create']);
    Route::put('edit', [BancoController::class, 'edit']);
    Route::delete('eliminar/{id}', [BancoController::class, 'eliminar']);

});


Route::group([
    'prefix' => 'asi',
], function () {
    Route::get('list', [AsientoController::class, 'list']);
    Route::get('getdata/{id}', [AsientoController::class, 'findById']);
    Route::get('getdocnum', [AsientoController::class, 'getByDocNum']);
    Route::post('create', [AsientoController::class, 'save']);
    Route::put('edit', [AsientoController::class, 'save']);
    Route::delete('eliminar/{id}', [AsientoController::class, 'eliminar']);

});
