<?php

use Illuminate\Http\Request;
use App\Http\Controllers\API\Backend\AnnonceController;
use App\Http\Controllers\API\Backend\AbonnementController;
use App\Http\Controllers\API\Backend\UserController;
use App\Http\Controllers\API\Backend\AdvertiserController;
use App\Http\Controllers\API\Backend\CategorieController;
use App\Http\Controllers\API\Backend\StripeControllers;
use App\Http\Controllers\API\Backend\PaymentController;
use App\Http\Controllers\API\Backend\DashboardAdminController;
use App\Http\Controllers\API\Backend\ModePaiementController;
use App\Http\Controllers\API\Frontend\DashboardController;
use App\Http\Controllers\Auth\LoginControleurAuth;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

/** Authentification */
Route::middleware(['cors', 'throttle:10,1'])->group(function () {
    Route::match(['post', 'options'], '/login', [LoginControleurAuth::class, 'login']);
});

/** ── Routes Admin ────────────────────────────────────────────────────── */

Route::middleware(['auth:sanctum', 'access.admin'])
    ->prefix('/users_back')->controller(UserController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('store', 'store');
        Route::get('/show/{id}', 'show');
        Route::put('/update/{id}', 'update');
        Route::delete('/delete/{id}', 'destroy');
    });

Route::middleware(['auth:sanctum', 'access.admin'])
    ->prefix('/dashboard_admin')->controller(DashboardAdminController::class)
    ->group(function () {
        Route::get('/', 'dashboard')->name('dashboard_admin');
    });

Route::middleware(['auth:sanctum', 'access.admin'])
    ->prefix('/advertiser_bac')->controller(AdvertiserController::class)
    ->group(function () {
        Route::patch('/change/{id}', 'status');
    });

Route::middleware(['auth:sanctum', 'access.admin'])
    ->prefix('/categorie_back')->controller(CategorieController::class)->group(function () {
        Route::get('/{nbr_categ}/{search?}', 'index');
        Route::post('store', 'store');
        Route::get('/show/{id}', 'show');
        Route::get('/search/{id}', 'show');
        Route::put('/update/{id}', 'update');
        Route::delete('/delete/{id}', 'destroy');
        Route::get('/annonce_by_categ/{array_categ}', 'categAnnonce');
    });

Route::middleware(['auth:sanctum', 'access.admin'])
    ->prefix('/annonce_back_admin')->controller(AnnonceController::class)->group(function () {
        Route::get('/{nbr_annonce}/{search?}', 'getAllAnnonce');
        Route::patch('/update_status/{id_annonce}/{new_status}', 'changeStatusAdmin')->name('changeStatusAdmin');
    });

Route::middleware(['auth:sanctum', 'access.admin'])
    ->prefix('/abonnement_back')->controller(AbonnementController::class)->group(function () {
        Route::get('/show/{id}', 'show');
        Route::patch('/status/{id}', 'statusAbonnement');
        Route::get('/{nbr_abonnement}/{search?}', 'index');
        Route::post('store', 'store');
        Route::put('/update/{id}', 'update');
        Route::delete('/{id}/delete', 'destroy');
    });

Route::middleware(['auth:sanctum', 'access.admin'])
    ->prefix('/mode_paiement_back')->controller(ModePaiementController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/changeStatus', 'status');
    });

/** ── Routes Annonceur ───────────────────────────────────────────────── */

Route::middleware(['auth:sanctum', 'access.advertiser'])
    ->prefix('/dashboard_advertiser')
    ->controller(DashboardController::class)
    ->group(function () {
        Route::get('/{id}', 'dashboard')->name('dashboard_advertiser');
    });

Route::post('/advertiser_back/store', [AdvertiserController::class, 'store']); // inscription publique

Route::middleware(['auth:sanctum'])
    ->prefix('/advertiser_back')->controller(AdvertiserController::class)->group(function () {
        Route::get('/{paginate}/{search?}', 'index');
        Route::put('/update/{id}', 'update');
        Route::get('/show/{id}', 'show');
        Route::delete('/delete/{id}', 'destroy');
    });

Route::middleware(['auth:sanctum'])
    ->prefix('/annonce_back')->controller(AnnonceController::class)->group(function () {
        Route::get('/{user_id}/{nbr_annonce}/{search?}', 'index');
        Route::get('/create', 'create');
        Route::post('store', 'store');
        Route::put('/update/{id}', 'update');
        Route::put('/update_old/{id}', 'update');
        Route::delete('/delete/{id}/{array_categ}', 'destroy');
        Route::patch('/update_status/{id_user}/{id_annonce}/{new_status}/{info_paiement?}', 'changeStatus')->name('changeStatus');
        Route::patch('/update_abonnement/{id_user}/{id_annonce}/{new_abonnement}', 'changeAbonnement')->name('changeAbonnement');
    });

Route::middleware(['auth:sanctum', 'access.advertiser'])
    ->prefix('/mode_paiement_advert')->controller(ModePaiementController::class)->group(function () {
        Route::get('/', 'indexAdvert');
    });

/** ── Routes partagées ───────────────────────────────────────────────── */

Route::middleware(['auth:sanctum'])
    ->controller(AdvertiserController::class)->group(function () {
        Route::put('/updatePassword/{id}', 'updatePassword');
    });

/** ── Routes publiques (visiteurs) ───────────────────────────────────── */

Route::get('/get_annonce/{id}/{lang?}', [AnnonceController::class, 'get_annonce']);
Route::get('/categorie_back_public/{lang?}', [CategorieController::class, 'ListingCategorie']);

Route::prefix('/home_back')->controller(DashboardController::class)->group(function () {
    Route::get('/{user?}/{lang?}', 'dashboard');
    Route::post('/trie', 'trie');
    Route::post('/contact', 'contact');
});

/** ── Mot de passe oublié ─────────────────────────────────────────────── */

Route::post('/forget-password', [PasswordResetLinkController::class, 'store']);
Route::post('/reset-password', [NewPasswordController::class, 'store']);
Route::get('/reset-password/{token}/{email?}', [NewPasswordController::class, 'verifyToken'])->name('password.reset');

/** ── Vérifications d'unicité (inscription) ───────────────────────────── */

Route::post('check-email', [AdvertiserController::class, 'checkEmailExists']);
Route::post('check-username', [AdvertiserController::class, 'checkUsernameExists']);
Route::post('check-phone', [AdvertiserController::class, 'checkPhoneExists']);
Route::post('check-password', [AdvertiserController::class, 'checkPasswordExists']);

/** ── Paiements ───────────────────────────────────────────────────────── */

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/stripe-payment', [StripeControllers::class, 'stripePayment'])->name('stripe.stripePaymentInitial');
    Route::post('/initiate-payment', [PaymentController::class, 'initiatePayment']);
    Route::get('/check-payment-status/{dataPaiement}', [PaymentController::class, 'checkPaymentStatus']);
});

Route::middleware(['verify.freemopay'])
    ->post('/webhook/freemopay/{abonnement_id}/{annonce_id}/{userId}/{paiementId}',
        [PaymentController::class, 'callbackPayment'])
    ->name('payment.callback');
