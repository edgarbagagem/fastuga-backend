<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\CustomerController;


use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\ProductController;
use App\Http\Controllers\api\OrderController;
use App\Http\Controllers\api\OrderItemController;

Route::post('login', [AuthController::class, 'login']);


//fora do grupo de autenticação pois é a rota tanto para criação de employees por parte do manager como para criação de conta
//por parte dos clientes
Route::post('users/register', [UserController::class, 'create']);

Route::middleware('auth:api')->group(function () {
    //users
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('users/me', [UserController::class, 'show_me']);
    Route::get('users', [UserController::class, 'index'])->middleware('can:viewAny,App\Models\User');
    Route::get('users/{user}', [UserController::class, 'show'])
        ->middleware('can:view,user');
    Route::put('users/{user}', [UserController::class, 'update'])
        ->middleware('can:update,user');
    Route::patch('users/{user}/password', [UserController::class, 'update_password'])
        ->middleware('can:updatePassword,user');
    Route::post('users/{user}/photo', [UserController::class, 'upload_photo'])->middleware('can:update,user');
    Route::delete('users/{user}', [UserController::class, 'delete_user'])->middleware('can:delete,user');
    //customers
    Route::get('customers/{id}', [CustomerController::class, 'show']);
    Route::put('customers/{id}', [CustomerController::class, 'update']);
    Route::get('customers/{id}/orders', [CustomerController::class, 'show_orders']);
    Route::put('customers/{id}/points', [CustomerController::class, 'update_points']);


    Route::get('menu/{product}', [ProductController::class, 'show']);
    Route::put('menu/{product}', [ProductController::class, 'update']);
    Route::delete('menu/{product}', [ProductController::class, 'delete']);

    Route::post('menu/create',  [ProductController::class, 'create']);

    //orders
    Route::get('orders/items/{id}', [OrderItemController::class, 'index']);
    Route::get('orders', [OrderController::class, 'index']);

    Route::put('orders/{item}', [OrderItemController::class, 'update']);

    Route::put('order/{order}', [OrderController::class, 'update']);



    Route::get('orders/preparing', [OrderController::class, 'show_preparing_orders'])->middleware('can:viewAny,App\Models\User');
    Route::put('orders/{order}/cancel', [OrderController::class, 'cancel_order']);


    Route::post('products/{product}/photo', [ProductController::class, 'upload_photo']);

    //Stats
    Route::get('users/{user}/stats', [UserController::class, 'show_stats'])->middleware('can:view,user');
});

Route::get('orders/employee/{id}', [OrderController::class, 'ordersForEmployees']);
Route::get('orders/employee/{id}/products', [OrderController::class, 'productsInOrdersForEmployees']);


Route::get('menu', [ProductController::class, 'index']);
Route::post('orders', [OrderController::class, 'store']);
Route::post('orders/items', [OrderItemController::class, 'store']);
