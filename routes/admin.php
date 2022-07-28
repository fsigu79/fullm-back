<?php
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\RespuestaController;
use App\Http\Controllers\Admin\DashboardController;


use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'admin',
], function () {

    Route::group([
        'prefix' => 'auth',
    ], function () {
        Route::post('login', [AuthController::class, 'login']);
    });


     Route::group([
        'prefix' => 'dashboard',
    ], function () {
        Route::get('data', [DashboardController::class, 'data']);
    });

    Route::group([
        'prefix' => 'client',
    ], function () {
        Route::get('data/{id}', [ClientController::class, 'getData']);
        Route::get('code/{id}', [ClientController::class, 'getCode']);
        Route::get('list', [ClientController::class, 'list']);
        Route::get('all', [ClientController::class, 'all']);
        Route::get('export', [ClientController::class, 'export']);
        Route::get('search-clients', [ClientController::class, 'searchClients']);
    });



    Route::group([
        'prefix' => 'role',
    ], function () {
        Route::get('list', [RoleController::class, 'list']);
        Route::post('store', [RoleController::class, 'store']);
        Route::put('update', [RoleController::class, 'update']);
        Route::get('all', [RoleController::class, 'all']);
    });

    Route::group([
        'prefix' => 'user',
    ], function () {
        //Route::get('list', [UserController::class, 'list']);
        Route::post('store', [UserController::class, 'store']);
        Route::put('update', [UserController::class, 'update']);
        Route::get('all', [UserController::class, 'all']);
        Route::get('profile/{auid}', [UserController::class, 'getUserProfile']);
        Route::put('update', [UserController::class, 'update']);
        Route::get('data/{id}', [UserController::class, 'getData']);
        Route::get('search-users', [UserController::class, 'searchUsers']);

    });

    Route::group([
        'prefix' => 'profile',
    ], function () {
        Route::get('{auid}', [UserController::class, 'getUserProfile']);
    });

    Route::group([
        'prefix' => 'account',
    ], function () {
        Route::put('change-password', [UserController::class, 'changePassword']);
    });

    Route::group([
        'prefix' => 'answer',
    ], function () {
        Route::get('list', [RespuestaController::class, 'list']);
        Route::post('create', [RespuestaController::class, 'create']);
        Route::put('update', [RespuestaController::class, 'update']);
        Route::get('all', [RespuestaController::class, 'all']);
        Route::get('export', [RespuestaController::class, 'export']);
        Route::post('add-image', [RespuestaController::class, 'addImage']);
        Route::get('comprobar/{id}', [RespuestaController::class, 'comprobar']);
        Route::get('search-answer', [RespuestaController::class, 'searchAnswer']);
    });



});
