<?php

use App\Http\Controllers\AuthApiController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderApiController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
// User Authentication
Route::post('/login', [AuthApiController::class, 'login'])->name('login');
Route::post('/register', [AuthApiController::class, 'register'])->name('register');

Route::get('/product', [ProductController::class, 'index']);
Route::get('/product/{id}', [ProductController::class, 'show']);

Route::middleware('auth:sanctum')->group(function() {
    Route::post('/logout', [AuthApiController::class, 'logout'])->name('logout');
    Route::apiResource('/product', ProductController::class)->only(['store', 'destroy']);
    Route::post('product/edit/{id}', [ProductController::class, 'update']);
    Route::apiResource('/category', CategoryController::class);
    Route::apiResource('/user', UserController::class)->only(['index', 'destroy', 'show']);
    Route::post('/user/change_role/{unique_id}', [UserController::class, 'changeRole']);
    Route::post('/user/edit/{uniqueId}', [UserController::class, 'update']);

    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::post('/cart/edit/{id}', [CartController::class, 'update']);

    Route::apiResource('/order', OrderApiController::class);

    Route::get('/chart', [ProductController::class, 'chart']);
});
