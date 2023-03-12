<?php

use App\Http\Controllers\Api\PaymentOtherController;
use App\Http\Controllers\Getnet\GetnetController;
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
Route::middleware(['chk_user_auth'])->group(function () {
    Route::get('/list-others', [PaymentOtherController::class, 'index']);
    Route::get('/getnet-list-brands', [GetnetController::class, 'getBrands']);
    Route::post('/getnet-card', [GetnetController::class, 'saveCard']);
    Route::get('/getnet-card/{card_id}', [GetnetController::class, 'getCardById']);
    Route::delete('/getnet-card/{card_id}', [GetnetController::class, 'removeCardById']);
    Route::get('/getnet-card/customer/{customer_id}', [GetnetController::class, 'getCardByCustomerId']);
    Route::post('/getnet-process-payment', [GetnetController::class, 'processPayment']);
    Route::post('/getnet-process-pix', [GetnetController::class, 'processPix']);
});

Route::get('/', function () use ($router) {
    // return $router->app->version();
    return response()->json(['message' => 'API Payment - Success']);
});