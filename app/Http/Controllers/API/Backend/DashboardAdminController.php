<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use App\Repositories\Backend\AnnonceRepository;
use App\Repositories\Backend\AbonnementRepository;
use App\Repositories\Backend\CategorieRepository;
use App\Repositories\Backend\UserRepository;
use App\Repositories\Backend\PaiementRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardAdminController extends Controller
{
        //
        protected $annonceRepository;
        protected $pictureController;
        protected $abonnementController;
        protected $abonnementRepository;
        protected $categorieRepository;
        protected $userRepository;
        protected $paiementRepository;
    
        public function __construct(AnnonceRepository $annonceRepository,  PictureController $pictureController,
                                    CategorieRepository $categorieRepository, AbonnementRepository $abonnementRepository, UserRepository $userRepository,
                                    PaiementRepository $paiementRepository)
        {
            $this->annonceRepository = $annonceRepository;
            $this->pictureController = $pictureController;
            $this->abonnementRepository = $abonnementRepository;
            $this->categorieRepository = $categorieRepository;
            $this->userRepository = $userRepository;
            $this->paiementRepository = $paiementRepository;
        }

    /** Dashbord admin */
    public function dashboard()
    {
        try{
            // ── KPIs principaux ──────────────────────────────────────────────
            $totalUsers        = $this->userRepository->getAllUsers();
            $totalAnnonces     = $this->annonceRepository->countAnnonce();
            $totalRevenue      = $this->paiementRepository->ChiffreAffaire();

            // ── Croissance ce mois vs mois dernier ───────────────────────────
            $newUsersThisMonth     = $this->userRepository->newUsersThisMonth();
            $newUsersLastMonth     = $this->userRepository->newUsersLastMonth();
            $newAnnoncesThisMonth  = $this->annonceRepository->newAnnoncesThisMonth();
            $newAnnoncesLastMonth  = $this->annonceRepository->newAnnoncesLastMonth();
            $revenueThisMonth      = $this->paiementRepository->revenueThisMonth();
            $revenueLastMonth      = $this->paiementRepository->revenueLastMonth();

            // ── Calcul des taux de croissance (en %) ─────────────────────────
            $userGrowthRate    = $newUsersLastMonth > 0
                ? round((($newUsersThisMonth - $newUsersLastMonth) / $newUsersLastMonth) * 100, 1)
                : ($newUsersThisMonth > 0 ? 100 : 0);
            $annonceGrowthRate = $newAnnoncesLastMonth > 0
                ? round((($newAnnoncesThisMonth - $newAnnoncesLastMonth) / $newAnnoncesLastMonth) * 100, 1)
                : ($newAnnoncesThisMonth > 0 ? 100 : 0);
            $revenueGrowthRate = $revenueLastMonth > 0
                ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
                : ($revenueThisMonth > 0 ? 100 : 0);

            // ── Tendances sur 6 mois ─────────────────────────────────────────
            $userGrowthTrend    = $this->userRepository->userGrowthTrend(6);
            $annonceTrend       = $this->annonceRepository->annonceTrend(6);
            $revenueMonthlyTrend = $this->paiementRepository->monthlyRevenueTrend(6);

            // ── Répartitions ─────────────────────────────────────────────────
            $progressAbonnement  = $this->abonnementRepository->progressAbonnement();
            $progressStatus      = $this->annonceRepository->progressStatusAnnonce();
            $usersByProfile      = $this->userRepository->usersByProfile();
            $revenueByMode       = $this->paiementRepository->revenueByMode();

            // ── Activité récente ─────────────────────────────────────────────
            $recentUsers     = $this->userRepository->recentUsers(5);
            $recentAnnonces  = $this->annonceRepository->recentAnnonces(5);
            $recentPayments  = $this->paiementRepository->recentPayments(5);

            // ── Classements ──────────────────────────────────────────────────
            $topAdvertisers  = $this->annonceRepository->topAdvertisers(5);
            $categPopulaire  = $this->categorieRepository->getCategPopulaire();

            // ── Géographie ───────────────────────────────────────────────────
            $usersByCountry    = $this->userRepository->usersByCountry(5);
            $annoncesByCountry = $this->annonceRepository->annoncesByCountry(5);

            // ── Utilisateurs bloqués ─────────────────────────────────────────
            $userLock = $this->userRepository->getUserLock();

            return response()->json([
                'success' => true,

                // KPIs globaux
                'total_users'    => $totalUsers,
                'total_annonces' => $totalAnnonces,
                'total_montant'  => $totalRevenue,

                // Croissance mensuelle
                'growth' => [
                    'users' => [
                        'this_month' => $newUsersThisMonth,
                        'last_month' => $newUsersLastMonth,
                        'rate'       => $userGrowthRate,
                    ],
                    'annonces' => [
                        'this_month' => $newAnnoncesThisMonth,
                        'last_month' => $newAnnoncesLastMonth,
                        'rate'       => $annonceGrowthRate,
                    ],
                    'revenue' => [
                        'this_month' => $revenueThisMonth,
                        'last_month' => $revenueLastMonth,
                        'rate'       => $revenueGrowthRate,
                    ],
                ],

                // Tendances sur 6 mois
                'trends' => [
                    'users'   => $userGrowthTrend,
                    'annonces' => $annonceTrend,
                    'revenue' => $revenueMonthlyTrend,
                ],

                // Répartitions
                'progress_abonnement' => $progressAbonnement,
                'progress_status'     => $progressStatus,
                'users_by_profile'    => $usersByProfile,
                'revenue_by_mode'     => $revenueByMode,

                // Activité récente
                'recent_users'    => $recentUsers,
                'recent_annonces' => $recentAnnonces,
                'recent_payments' => $recentPayments,

                // Classements
                'top_advertisers'  => $topAdvertisers,
                'tab_categ_popular' => $categPopulaire,

                // Géographie
                'geo' => [
                    'users'    => $usersByCountry,
                    'annonces' => $annoncesByCountry,
                ],

                // Modération
                'tab_user_lock' => $userLock,
            ]);

        }catch(Exception $e){
            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);
        }
    }
}


