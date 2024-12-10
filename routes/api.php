<?php

use Illuminate\Http\Request;
use App\Http\Controllers\API\Backend\AnnonceController;
use App\Http\Controllers\API\Backend\AbonnementController;
use App\Http\Controllers\API\Backend\UserController;
use App\Http\Controllers\API\Backend\AdvertiserController;
use App\Http\Controllers\API\Backend\CategorieController;
use App\Http\Controllers\API\Backend\SousCategorieController;
use App\Http\Controllers\API\Backend\DashboardAdminController;
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

// Route::middleware(['cors'])->group(function () {
//     /**Route liée à la categorie */
//     Route::prefix('/categorie_back')->controller(CategorieController::class)->group(function(){
//         Route::get('/', 'index');
//         Route::post('store','store');
//         Route::get('/show/{id}', 'show');
//         Route::put('/update/{id}', 'update');
//         Route::delete('/delete/{id}', 'destroy');
//     });
// });


Route::prefix('/users_back')->controller(UserController::class)->group(function(){
    Route::get('/', 'index');
    Route::post('store','store');
    Route::get('/show/{id}', 'show');
    Route::put('/update/{id}', 'update');
    Route::delete('/delete/{id}', 'destroy');
});

/**Route dashboard admin */
Route::middleware(['auth:sanctum','access.admin'])
    ->prefix('/dashboard_admin')
    ->controller(DashboardAdminController::class)
    ->group(function(){
    Route::get('/{id}', 'dashboard')->name('dashboard_admin');
});

/**Route dashboard advertiser */
Route::middleware(['auth:sanctum','access.advertiser'])
    ->prefix('/dashboard_advertiser')
    ->controller(DashboardController::class)
    ->group(function(){
    Route::get('/{id}', 'dashboard')->name('dashboard_advertiser');
});


/**Route des users ayant pour status annonceur */
Route::prefix('/advertiser_back')->controller(AdvertiserController::class)->group(function(){
    Route::get('/{paginate}/{search?}', 'index');
    Route::post('store','store');
    Route::post('/update/{id}', 'update');
    Route::delete('/delete/{id}', 'destroy');
    // Route::get('/change/{id}', 'status');
});

/**Route des users ayant pour status annonceur */
Route::prefix('/advertiser_ba')->controller(AdvertiserController::class)->group(function(){
    Route::get('/show/{id}', 'show');
});

/**Route pour changer le status d'un annonceur */
Route::middleware(['auth:sanctum','access.admin'])
    ->prefix('/advertiser_bac')->controller(AdvertiserController::class)->group(function(){
    Route::get('/change/{id}', 'status');
});



/**Route liée à la categorie */
Route::middleware(middleware: ['auth:sanctum','access.admin'])
    ->prefix('/categorie_back')->controller(CategorieController::class)->group(function(){
    Route::get('/{nbr_categ}/{search?}', 'index');
    Route::post('store','store');
    Route::get('/show/{id}', 'show');
    Route::get('/search/{id}', 'show');
    Route::put('/update/{id}', 'update');
    Route::delete('/delete/{id}', 'destroy');
    Route::get('/annonce_by_categ/{array_categ}', 'categAnnonce');
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
Route::middleware(['auth:sanctum','access.advertiser'])
    ->prefix('/annonce_back')->controller(AnnonceController::class)->group(function(){
        Route::get('/{user_id}/{nbr_annonce}/{search?}', 'index');
        Route::get('/create', 'create');
        Route::post('store','store');
        Route::put('/update/{id}', 'update');
        Route::delete('/delete/{id}/{array_categ}', 'destroy');
        // Route::get('/dashboard/{id}', 'dashboard')->name('dashboard')->middleware(['auth:sanctum, dashboard.advertiser']);
        Route::get('/update_status/{id_user}/{id_annonce}/{new_status}', 'changeStatus')->name('changeStatus');
        Route::get('/update_abonnement/{id_user}/{id_annonce}/{new_abonnement}', 'changeAbonnement')->name('changeAbonnement');
});
Route::get('/get_annonce/{id}',[AnnonceController::class, 'get_annonce']);

/**Route liée aux abonnements */
Route::middleware(['auth:sanctum','access.admin'])
    ->prefix('/abonnement_back')->controller(AbonnementController::class)->group(function(){
    Route::get('/', 'index');
    Route::post('store','store');
    Route::get('/show/{id}', 'show');
    Route::put('/update/{id}', 'update');
    Route::delete('/{id}/delete', 'destroy');
});

/**Route index visiteur */
Route::prefix('/home_back')->controller(DashboardController::class)->group(function(){
    Route::get('/', 'dashboard');
    Route::post('/trie', 'trie');
});


// Route pour traiter la soumission du formulaire de connexion
Route::post('/login', [LoginControleurAuth::class, 'login'])->name('login');


// /** Route Interface des advertisers */
// Route::prefix('/interface_advertiser')->controller(AnnonceController::class)->group(function(){
//     Route::get('/', 'index');
//     Route::post('store','store');
//     Route::get('/{id}/show', 'show');
//     Route::put('/{id}/update', 'update');
//     Route::delete('/{id}/delete', 'destroy');
// });

