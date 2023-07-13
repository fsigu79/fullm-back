<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DebitoClienteController;
use App\Http\Controllers\CreditoClienteController;
use App\Http\Controllers\TransportistaController;
use App\Http\Controllers\AbonoController;
use App\Http\Controllers\GuiaRemisionController;
use App\Http\Controllers\GuiasProductosController;
use App\Http\Controllers\DireccionController;

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
    'prefix' => 'customer',
], function () {
    Route::get('list', [CustomerController::class, 'list']);
    Route::get('customerId/{id}', [CustomerController::class, 'getRuc']);
    Route::get('customerbyId/{id}', [CustomerController::class, 'getById']);
    Route::get('search', [CustomerController::class, 'searchCustomer']);
    Route::post('create', [CustomerController::class, 'create']);
    Route::put('edit', [CustomerController::class, 'edit']);
    Route::delete('delete/{id}', [CustomerController::class, 'eliminar']);
    Route::get('docxpagar', [CustomerController::class, 'getDocumentosPendientesByCliente']);
});



Route::group([
    'prefix' => 'paymentm',
], function () {
    Route::get('list', [PaymentMethodController::class, 'all']);
    Route::get('{id}', [PaymentMethodController::class, 'getById']);

});


Route::group([
   'prefix' => 'price',
], function () {
   Route::get('list', [PriceController::class, 'list']);
   Route::get('{product}/{price}', [PriceController::class, 'getPrice']);
   Route::get('{id}', [PriceController::class, 'findById']);
   Route::get('detalle/list/prod', [PriceController::class, 'preciosDetalle']);
   Route::post('create', [PriceController::class, 'save']);
});

Route::group([
   'prefix' => 'invoice',
], function () {

   Route::get('list', [InvoiceController::class, 'list']);
   Route::post('create', [InvoiceController::class, 'save']);
   Route::put('edit', [InvoiceController::class, 'save']);
   Route::get('list/{id}', [InvoiceController::class, 'findById']);
});


Route::group([
    'prefix' => 'debito',
 ], function () {

    Route::get('list', [DebitoClienteController::class, 'list']);
    Route::post('create', [DebitoClienteController::class, 'save']);
    Route::get('list/{id}', [DebitoClienteController::class, 'findById']);
 });


Route::group([
   'prefix' => 'credito',
], function () {

   Route::get('list', [CreditoClienteController::class, 'list']);
   Route::post('create', [CreditoClienteController::class, 'save']);
   Route::put('edit', [CreditoClienteController::class, 'save']);
   Route::get('list/{id}', [CreditoClienteController::class, 'findById']);
});


Route::group([
    'prefix' => 'trans',
], function () {
    Route::get('list', [TransportistaController::class, 'list']);
    Route::get('listActive', [TransportistaController::class, 'list_active']);
    Route::get('tranbyId/{id}', [TransportistaController::class, 'getById']);
    Route::post('create', [TransportistaController::class, 'create']);
    Route::put('edit', [TransportistaController::class, 'edit']);
    Route::delete('delete/{id}', [TransportistaController::class, 'delete']);

});


Route::group([
   'prefix' => 'abo',
], function () {

   Route::get('list', [AbonoController::class, 'list']);
   Route::get('dsaldo/{id}', [AbonoController::class, 'documentosConSaldo']);
   Route::post('create', [AbonoController::class, 'save']);
   Route::put('edit', [AbonoController::class, 'save']);
   Route::get('list/{id}', [AbonoController::class, 'findById']);
});


Route::group([
    'prefix' => 'guiar',
 ], function () {

    Route::get('list', [GuiaRemisionController::class, 'list']);
    Route::post('create', [GuiaRemisionController::class, 'save']);
    Route::get('list/{id}', [GuiaRemisionController::class, 'findById']);

    Route::get('catseries', [GuiasProductosController::class, 'importaCatalogoSeries']);
    Route::get('getcatalogo', [GuiasProductosController::class, 'obtenerCatalogoSeries']);
    Route::get('download_xml/{id}', [GuiaRemisionController::class, 'downloadXML']);
    Route::get('download_pdf/{id}', [GuiaRemisionController::class, 'downloadPdf']);
    Route::get('resend_email/{id}', [GuiaRemisionController::class, 'resendEmail']);
    Route::get('resend_sri/{id}', [GuiaRemisionController::class, 'resendToSri']);

 });


Route::group([
   'prefix' => 'dir',
], function () {
   Route::get('list', [DireccionController::class, 'list']);
   Route::get('list/{id}', [DireccionController::class, 'getById']);
   Route::get('codpac/{codcli}', [DireccionController::class, 'getByCodigoPac']);
   Route::post('create', [DireccionController::class, 'create']);
   Route::put('edit', [DireccionController::class, 'update']);
   Route::delete('delete/{id}', [DireccionController::class, 'delete']);
});
