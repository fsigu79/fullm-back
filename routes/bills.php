<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\CuentaGastoController;
use App\Http\Controllers\SriController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\LiquidacionCompraController;
use App\Http\Controllers\ValeCajaController;
use App\Http\Controllers\AnticipoController;
use App\Http\Controllers\CreditoProveedorController;
use App\Http\Controllers\DebitoProveedorController;

/*
|--------------------------------------------------------------------------
| API Routes f
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
    'prefix' => 'proveedor',
], function () {
    Route::get('list', [ProveedorController::class, 'list']);
    Route::get('proveedorRuc/{id}', [ProveedorController::class, 'getRuc']);
    Route::get('proveedorbyid/{id}', [ProveedorController::class, 'getById']);
    Route::get('search', [ProveedorController::class, 'searchProveedor']);
    Route::post('create', [ProveedorController::class, 'create']);
    Route::put('edit', [ProveedorController::class, 'edit']);
    Route::delete('delete/{id}', [ProveedorController::class, 'eliminar']);
    Route::get('docxpagar', [ProveedorController::class, 'getDocumentosPendientesByProveedor']);
});


Route::group([
    'prefix' => 'ctagasto',
], function () {
    Route::get('list', [CuentaGastoController::class, 'list']);
    Route::post('create', [CuentaGastoController::class, 'create']);
    Route::put('edit', [CuentaGastoController::class, 'edit']);
    Route::delete('delete/{id}', [CuentaGastoController::class, 'eliminar']);
});


Route::group([
    'prefix' => 'sri',
], function () {
    Route::get('ret/{tabla}', [SriController::class, 'listRetencion']);

});



Route::group([
   'prefix' => 'compra',
], function () {

   Route::get('list', [CompraController::class, 'list']);
   Route::post('create', [CompraController::class, 'save']);
   Route::put('edit', [CompraController::class, 'save']);
   Route::get('list/{id}', [CompraController::class, 'findById']);
   Route::get('detalle/{id}', [CompraController::class, 'findById']);
});

Route::group([
   'prefix' => 'servi',
], function () {

   Route::get('list', [ServicioController::class, 'list']);
   Route::post('create', [ServicioController::class, 'save']);
   Route::put('edit', [ServicioController::class, 'save']);
   Route::get('list/{id}', [ServicioController::class, 'findById']);
});

Route::group([
   'prefix' => 'vales',
], function () {

   Route::get('list', [ValeCajaController::class, 'list']);
   Route::post('create', [ValeCajaController::class, 'save']);
   Route::put('edit', [ValeCajaController::class, 'save']);
   Route::get('list/{id}', [ServicValeCajaControllerioController::class, 'findById']);
});

Route::group([
   'prefix' => 'liquida',
], function () {

   Route::get('list', [LiquidacionCompraController::class, 'list']);
   Route::post('create', [LiquidacionCompraController::class, 'save']);
   Route::put('edit', [LiquidacionCompraController::class, 'save']);
   Route::get('list/{id}', [LiquidacionCompraController::class, 'findById']);
});


Route::group([
   'prefix' => 'pago',
], function () {

   Route::get('list', [PagoController::class, 'list']);
   Route::get('csaldo/{id}', [PagoController::class, 'documentosConSaldo']);
   Route::post('create', [PagoController::class, 'save']);
   Route::put('edit', [PagoController::class, 'save']);
   Route::get('list/{id}', [PagoController::class, 'findById']);
});


Route::group([
   'prefix' => 'anticipop',
], function () {

   Route::get('list', [AnticipoController::class, 'list']);
   Route::post('create', [AnticipoController::class, 'create']);
   Route::put('edit', [AnticipoController::class, 'edit']);
   Route::get('list/{id}', [AnticipoController::class, 'findById']);
});


Route::group([
   'prefix' => 'credito',
], function () {

   Route::get('list', [CreditoProveedorController::class, 'list']);
   Route::post('create', [CreditoProveedorController::class, 'save']);
   Route::put('edit', [CreditoProveedorController::class, 'save']);
   Route::get('list/{id}', [CreditoProveedorController::class, 'findById']);
});


Route::group([
   'prefix' => 'debito',
], function () {

   Route::get('list', [DebitoProveedorController::class, 'list']);
   Route::post('create', [DebitoProveedorController::class, 'save']);
   Route::put('edit', [DebitoProveedorController::class, 'save']);
   Route::get('list/{id}', [DebitoProveedorController::class, 'findById']);
});
