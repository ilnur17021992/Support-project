<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TelegramController;

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
Route::any('/bot', TelegramController::class);

Route::prefix('/user')->middleware('auth:sanctum')->group(function () {
    Route::get('/', function (Request $request) {
        return $request->user();
    });

    Route::prefix('/ticket')->group(function () {
        Route::get('/{id}/message', [TicketController::class, 'messages']);
        Route::post('/{id}/message', [TicketController::class, 'storeMessage']);
        Route::apiResource('/', TicketController::class);
    });
});
