<?php

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
Route::get('/getnet-list-brands', [GetnetController::class, 'getBrands']);
Route::post('/getnet-cards/card', [GetnetController::class, 'saveCard']);
Route::get('/getnet-cards/card/{card_id}', [GetnetController::class, 'getCardByCardId']);
Route::delete('/getnet-cards/card/{card_id}', [GetnetController::class, 'removeCardByCardId']);
Route::get('/getnet-cards/customer/{customer_id}', [GetnetController::class, 'listCardsByCustomerId']);
Route::post('/getnet-process-payment', [GetnetController::class, 'processPayment']);
Route::post('/getnet-process-payment', [GetnetController::class, 'processPayment']);

Route::get('/', function () use ($router) {
    // return $router->app->version();
    return response()->json(['message' => 'API Payment - Success']);
});