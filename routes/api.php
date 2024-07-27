<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebHooksController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'Api'], function () {

    Route::group(['prefix' => 'v1'], function () {
        Route::post('/register', 'AuthController@register');
        Route::post('/login', 'AuthController@login');

        // products
        Route::apiResource('/products', 'ProductController');

        // categories
        Route::apiResource('/categories', 'CategoryController');

        // orders
        Route::apiResource('/orders', 'OrdersController')->middleware('auth:api');
    });
});
