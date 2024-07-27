<?php

use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\ProductController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|'
*/


Auth::routes();

Route::get('/', fn () => redirect('/products'));

Route::resource('/categories', CategoryController::class);
Route::resource('/products', ProductController::class);


Route::resource('/orders', OrderController::class)->middleware('auth');
Route::put('change-order-status', [OrderController::class, 'changeOrderStatus'])
    ->middleware('auth')->name('orders.changeStatus');
