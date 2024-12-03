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
    public function dashboard($user_id)
    {
        try{
            $user = $this->userRepository->getById($user_id);
            $allAnnonce = $this->annonceRepository->getAllAnnonce(null, 5);
            $annoncePublisher = $this->annonceRepository->getAnnoncePublisher(null);
            $annonceExpired = $this->annonceRepository->getAnnonceExpired(null);
            $annonceInProgress = $this->annonceRepository->getAnnonceInProgress(null);
            $annoncePause = $this->annonceRepository->getAnnoncePause(null);

            if(isset($allAnnonce)){
                return response()->json([
                    'success' => true,
                    'user' => $user,
                    'annonces' => $allAnnonce,
                    'annonce_qte_publisher' => $annoncePublisher,
                    'annonce_qte_expired' => $annonceExpired,
                    'annonce_qte_inprogress' => $annonceInProgress,
                    'annonce_qte_pause' => $annoncePause,
                ]);
            }
            else{
                return response()->json([
                    'success' => false,
                    'user' => $user,
                    'annonces' => $allAnnonce,
                ]);
            }
        }catch(Exception $e){
            return response()->json($e);
        }
    }
}
