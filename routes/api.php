<?php

use Illuminate\Http\Request;
use App\Http\Controllers\API\Backend\UserController;
use App\Http\Controllers\API\Backend\AdvertiserController;
use App\Models\Annonce;
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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('/api_users_back')->controller(UserController::class)->group(function(){
    Route::get('/', 'index');
    Route::get('store','store');
    Route::get('/{id}/show', 'show');
    Route::put('/{id}/update', 'update');
    Route::delete('/{id}/delete', 'destroy');
});

/**Route des users ayant pour status annonceur */
Route::prefix('/api_advertiser_back')->controller(AdvertiserController::class)->group(function(){
    Route::get('/', 'index');
    Route::post('store','store');
    Route::get('/{id}/show', 'show');
    Route::put('/{id}/update', 'update');
    Route::delete('/{id}/delete', 'destroy');
});