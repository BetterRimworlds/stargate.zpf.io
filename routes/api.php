<?php

use App\Http\Controllers\GateController;
use Illuminate\Http\Request;

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

Route::post('/gate/{address}', [GateController::class, 'transmit']);
Route::delete('/gate/{address}', [GateController::class, 'receive']);


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
