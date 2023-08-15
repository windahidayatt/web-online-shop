<?php

use App\Http\Controllers\Backoffice\OrderController as BackofficeOrderController;
use App\Http\Controllers\Backoffice\ProductController as BackofficeProductController;
use App\Http\Controllers\Backoffice\SummaryController as BackofficeSummaryController;
use App\Http\Controllers\Frontoffice\AuthController;
use App\Http\Controllers\Frontoffice\CartController;
use App\Http\Controllers\Frontoffice\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::get('/test', function () {
    return "hello!";
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group( function () {
    Route::post('logout', [AuthController::class, 'logout']);
    
    Route::post('/cart', [CartController::class, 'store']);

    Route::post('/checkout', [OrderController::class, 'store']);

    Route::get('/product', [BackofficeProductController::class, 'index']);
    Route::get('/product/{id}', [BackofficeProductController::class, 'show']);
    Route::post('/product', [BackofficeProductController::class, 'store']);

    Route::get('/order', [BackofficeOrderController::class, 'index']);

    Route::get('/summary-order', [BackofficeSummaryController::class, 'order_summary']);
});

