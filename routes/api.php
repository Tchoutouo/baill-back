<?php

use Illuminate\Http\Request;
use App\Http\Controllers\API\Backend\AnnonceController;
use App\Http\Controllers\API\Backend\AbonnementController;
use App\Http\Controllers\API\Backend\UserController;
use App\Http\Controllers\API\Backend\AdvertiserController;
use App\Http\Controllers\API\Backend\CategorieController;
use App\Http\Controllers\API\Backend\SousCategorieController;
use App\Http\Controllers\API\Backend\StripeControllers;
use App\Http\Controllers\API\Backend\PaymentController;
use App\Http\Controllers\API\Backend\DashboardAdminController;
use App\Http\Controllers\API\Backend\ModePaiementController;
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


// Route::options('{any}', function () {
//     return response('', 200)
//     ->header('Access-Control-Allow-Origin', '*')
//     ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
//     ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Application');
// })->where('any', '.*');



Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['cors'])->group(function () {
    Route::match(['post', 'options'], '/login',[LoginControleurAuth::class, 'login']);
});


Route::prefix('/users_back')->controller(UserController::class)->group(function(){
    Route::get('/', 'index');
    Route::post('store','store');
    Route::get('/show/{id}', 'show');
    Route::put('/update/{id}', 'update');
    Route::delete('/delete/{id}', 'destroy');
});

/**--------------       Listing des routes Admin         ------------*/


        /**Route dashboard admin */
        Route::middleware(['auth:sanctum','access.admin'])
            ->prefix('/dashboard_admin')
            ->controller(DashboardAdminController::class)
            ->group(function(){
            Route::get('/', 'dashboard')->name('dashboard_admin');
        });


        /**Route pour changer le status d'un annonceur */
        Route::middleware(['auth:sanctum','access.admin'])
        ->prefix('/advertiser_bac')->controller(AdvertiserController::class)->group(function(){
        Route::get('/change/{id}', 'status');
        });


        /**Route liée à la categorie */
        Route::middleware(['auth:sanctum','access.admin'])
            ->prefix('/categorie_back')->controller(CategorieController::class)->group(function(){
            Route::get('/{nbr_categ}/{search?}', 'index');
            Route::post('store','store');
            Route::get('/show/{id}', 'show');
            Route::get('/search/{id}', 'show');
            Route::put('/update/{id}', 'update');
            Route::delete('/delete/{id}', 'destroy');
            Route::get('/annonce_by_categ/{array_categ}', 'categAnnonce');
        });

        
        /**Route des annonces admin*/
        Route::middleware(['auth:sanctum','access.admin'])
        ->prefix('/annonce_back_admin')->controller(AnnonceController::class)->group(function(){
            Route::get('/{nbr_annonce}/{search?}', 'getAllAnnonce');
            Route::get('/update_status/{id_annonce}/{new_status}', 'changeStatusAdmin')->name('changeStatusAdmin');
        });

        
        /**Route liée aux abonnements */
        Route::middleware(['auth:sanctum'])
        ->prefix('/abonnement_back')->controller(AbonnementController::class)->group(function(){
            Route::get('/show/{id}', 'show');
            Route::get('/status/{id}', 'statusAbonnement');
            Route::get('/{nbr_abonnement}/{search?}', 'index');
            Route::post('store','store');
            Route::put('/update/{id}', 'update');
            Route::delete('/{id}/delete', 'destroy');
        });
        
        /**Route liée aux modes de paiement */
        Route::middleware(['auth:sanctum','access.admin'])
            ->prefix('/mode_paiement_back')->controller(ModePaiementController::class)->group(function(){
                Route::get('/', 'index');
                Route::get('/changeStatus/{arrayPayment}', 'status');
        });

/**------------------- Fin des routes admin ------------------------------------- */



/**------------------- Listing des routes Advertiser/Bailleur ------------------- */

        /**Route dashboard advertiser */
        Route::middleware(['auth:sanctum','access.advertiser'])
            ->prefix('/dashboard_advertiser')
            ->controller(App\Http\Controllers\API\Backend\DashboardController::class)
            ->group(function(){
            Route::get('/{id}', 'dashboard')->name('dashboard_advertiser');
        });


        /**Route des users ayant pour status annonceur */
        Route::prefix('/advertiser_back')->controller(AdvertiserController::class)->group(function(){
            Route::get('/{paginate}/{search?}', 'index');
            Route::post('store','store');
            Route::put('/update/{id}', 'update');
            Route::get('/show/{id}', 'show');
            Route::delete('/delete/{id}', 'destroy'); 
        });

        /**Route liée l'annonce*/
        Route::middleware(['auth:sanctum'])
        ->prefix('/annonce_back')->controller(AnnonceController::class)->group(function(){
            Route::get('/{user_id}/{nbr_annonce}/{search?}', 'index');
            Route::get('/create', 'create');
            Route::post('store','store');
            Route::put('/update/{id}', 'update');
            Route::put('/update_old/{id}', 'update');
            Route::delete('/delete/{id}/{array_categ}', 'destroy');
            // Route::get('/dashboard/{id}', 'dashboard')->name('dashboard')->middleware(['auth:sanctum, dashboard.advertiser']);
            Route::get('/update_status/{id_user}/{id_annonce}/{new_status}/{info_paiement?}', 'changeStatus')->name('changeStatus');
            Route::get('/update_abonnement/{id_user}/{id_annonce}/{new_abonnement}', 'changeAbonnement')->name('changeAbonnement');
        });

        /**Route liée aux modes de paiement */
            Route::middleware(['auth:sanctum','access.advertiser'])
            ->prefix('/mode_paiement_advert')->controller(ModePaiementController::class)->group(function(){
                Route::get('/', 'indexAdvert');
                // Route::get('/changeStatus/{id}', 'status');
        });

/**------------------- Fin des routes advertiser/bailleur ------------------------------------- */


/**------------------- Routes des visiteurs ------------------------------------- */

    Route::get('/get_annonce/{id}',[AnnonceController::class, 'get_annonce']);

    Route::get('/categorie_back_public',[CategorieController::class,'ListingCategorie']);

    /**Route index visiteur */
    Route::prefix('/home_back')->controller(DashboardController::class)->group(function(){
        Route::get('/', 'dashboard');
        Route::post('/trie', 'trie');
        Route::post('/contact', 'contact');
    });

/**------------------- Fin des routes des visiteurs ------------------------------------- */



/**------------------- Routes partagées Admin et Advertiser ------------------------------------- */
    /**Route update password */
    Route::middleware('auth:sanctum')
        ->controller(AdvertiserController::class)
        ->group(function(){
        Route::put('/updatePassword/{id}', 'updatePassword');
        Route::put('/forgetPassword/{id}', 'forgetPassword');
    });

// Route pour traiter la soumission du formulaire de connexion
// Route::post('/login', [LoginControleurAuth::class, 'login'])->name('login');
// Route::get('/login', [LoginControleurAuth::class, 'login'])->name('login');



Route::post('/stripe-payment', [StripeControllers::class, 'stripePayment'])->name('stripe.stripePaymentInitial');

/** Route paiement mobile */
Route::post('/initiate-payment', [PaymentController::class, 'initiatePayment']);
Route::get('/check-payment-status/{dataPaiement}/', [PaymentController::class, 'checkPaymentStatus']);
Route::post('/webhook/freemopay/{abonnement_id}/{annonce_id}/{userId}/{paiementId}', [PaymentController::class, 'callbackPayment'])->name('payment.callback');
