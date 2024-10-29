<?php

use Illuminate\Http\Request;
use App\Http\Controllers\API\Backend\AnnonceController;
use App\Http\Controllers\API\Backend\AbonnementController;
use App\Http\Controllers\API\Backend\UserController;
use App\Http\Controllers\API\Backend\AdvertiserController;
use App\Http\Controllers\API\Backend\CategorieController;
use App\Http\Controllers\API\Backend\SousCategorieController;
use App\Http\Controllers\API\Frontend\DashboardController;
use App\Http\Controllers\Auth\LoginControleurAuth;

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


Route::prefix('/users_back')->controller(UserController::class)->group(function(){
    Route::get('/', 'index');
    Route::post('store','store');
    Route::get('/show/{id}', 'show');
    Route::put('/update/{id}', 'update');
    Route::delete('/delete/{id}', 'destroy');
});

/**Route des users ayant pour status annonceur */
Route::prefix('/advertiser_back')->controller(AdvertiserController::class)->group(function(){
    Route::get('/', 'index');
    Route::post('store','store');
    Route::get('/show/{id}', 'show');
    Route::put('/update/{id}', 'update');
    Route::delete('/delete/{id}', 'destroy');
});



/**Route liée à la categorie */
Route::prefix('/categorie_back')->controller(CategorieController::class)->group(function(){
    Route::get('/', 'index');
    Route::post('store','store');
    Route::get('/show/{id}', 'show');
    Route::put('/update/{id}', 'update');
    Route::delete('/delete/{id}', 'destroy');
});


/**Route liée à la sous-categorie */
Route::prefix('/sous_categorie_back')->controller(SousCategorieController::class)->group(function(){
    Route::get('/', 'index');
    Route::post('store','store');
    Route::get('/show/{id}', 'show');
    Route::put('/update/{id}', 'update');
    Route::delete('/delete/{id}', 'destroy');
});

/**Route liée l'annonce */
Route::prefix('/annonce_back')->controller(AnnonceController::class)->group(function(){
    Route::get('/{user_id}/{nbr_annonce}', 'index');
    Route::get('/create', 'create');
    Route::post('store','store');
    Route::get('/shows/{id}', 'shows');
    Route::put('/update/{id}', 'update');
    Route::delete('/delete/{id}', 'destroy');
    Route::get('/dashboard/{id}', 'dashboard')->name('dashboard');
    Route::get('/update_status/{id_user}/{id_annonce}/{new_status}', 'changeStatus')->name('changeStatus');
});

/**Route liée aux abonnements */
Route::prefix('/abonnement_back')->controller(AbonnementController::class)->group(function(){
    Route::get('/', 'index');
    Route::post('store','store');
    Route::get('/{id}/show', 'show');
    Route::put('/{id}/update', 'update');
    Route::delete('/{id}/delete', 'destroy');
});

/**Route index visiteur */
Route::prefix('/home_back')->controller(DashboardController::class)->group(function(){
    Route::get('/', 'dashboard');
});


// Route pour traiter la soumission du formulaire de connexion
Route::post('/login', [LoginControleurAuth::class, 'login']);


// /** Route Interface des advertisers */
// Route::prefix('/interface_advertiser')->controller(AnnonceController::class)->group(function(){
//     Route::get('/', 'index');
//     Route::post('store','store');
//     Route::get('/{id}/show', 'show');
//     Route::put('/{id}/update', 'update');
//     Route::delete('/{id}/delete', 'destroy');
// });

