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

            $totalUsers = $this->userRepository->getAllUsers();

            $totalAnnonce = $this->annonceRepository->countAnnonce();

            $totalAccount = $this->paiementRepository->ChiffreAffaire();

            $progressAbonnement = $this->abonnementRepository->progressAbonnement();

            $progressStatusAnnonce = $this->annonceRepository->progressStatusAnnonce();
            
            $userLock = $this->userRepository->getUserLock();

            $categPopulaire = $this->categorieRepository->getCategPopulaire();

            return response()->json([
                'success' => true,
                'total_users' => $totalUsers,
                'total_annonces' => $totalAnnonce,
                'total_montant' => $totalAccount,
                'progress_abonnement' => $progressAbonnement,
                'progress_status' => $progressStatusAnnonce,
                'tab_user_lock' => $userLock,
                'tab_categ_popular' => $categPopulaire,
            ]);

        }catch(Exception $e){
            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);
        }
    }
}


