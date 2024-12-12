<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use App\Repositories\Backend\AnnonceRepository;
use App\Repositories\Backend\AbonnementRepository;
use App\Repositories\Backend\CategorieRepository;
use App\Repositories\Backend\UserRepository;
use Exception;
use Illuminate\Http\Request;

class DashboardAdminController extends Controller
{
        //
        protected $annonceRepository;
        protected $pictureController;
        protected $abonnementController;
        protected $abonnementRepository;
        protected $categorieRepository;
        protected $userRepository;
    
        public function __construct(AnnonceRepository $annonceRepository,  PictureController $pictureController, 
        CategorieRepository $categorieRepository, AbonnementRepository $abonnementRepository, UserRepository $userRepository)
        {
            $this->annonceRepository = $annonceRepository;
            $this->pictureController = $pictureController;
            $this->abonnementRepository = $abonnementRepository;
            $this->categorieRepository = $categorieRepository;
            $this->userRepository = $userRepository;
        }

    /** Dashbord admin */
    public function dashboard()
    {
        try{

            $totalUsers = $this->userRepository->getAllUsers();

            $totalAnnonce = $this->annonceRepository->countAnnonce();

            $progressAbonnement = $this->abonnementRepository->progressAbonnement();

            $progressStatusAnnonce = $this->annonceRepository->progressStatusAnnonce();
            
            $userLock = $this->userRepository->getUserLock();

            $categPopulaire = $this->categorieRepository->getCategPopulaire();

            return response()->json([
                'success' => true,
                'total_users' => $totalUsers,
                'total_annonces' => $totalAnnonce,
                'progress_abonnement' => $progressAbonnement,
                'progress_status' => $progressStatusAnnonce,
                'tab_user_lock' => $userLock,
                'tab_categ_popular' => $categPopulaire,
            ]);

        }catch(Exception $e){
            return response()->json($e);
        }
    }
}
