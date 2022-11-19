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
Route::get('/getnet-cards/customer/{customer_id}', [GetnetController::class, 'listCardsByCustomerId']);
Route::get('/getnet-cards/{card_id}', [GetnetController::class, 'getCardByCardId']);
Route::get('/getnet-cards/remove/{card_id}', [GetnetController::class, 'removeCardByCardId']);
Route::get('/getnet-list-brands', [GetnetController::class, 'getBrands']);
Route::post('/getnet-process-payment', [GetnetController::class, 'processPayment']);

Route::get('/', function () use ($router) {
    // return $router->app->version();
    return response()->json(['message' => 'API Payment - Success']);
});