<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use App\Repositories\Backend\AnnonceRepository;
use App\Repositories\Backend\AbonnementRepository;
use App\Repositories\Backend\CategorieRepository;
use App\Repositories\Backend\UserRepository;
use Exception;
use Illuminate\Http\Request;

class DashboardController extends Controller
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

    /** Dashbord d'un annonceur */
    public function dashboard($user_id)
    {
        try{
            $user = $this->userRepository->getById($user_id);
            $allAnnonce = $this->annonceRepository->getAllAnnonce($user->id, 5);
            $annoncePublisher = $this->annonceRepository->getAnnoncePublisher($user->id);
            $annonceExpired = $this->annonceRepository->getAnnonceExpired($user->id);
            $annonceInProgress = $this->annonceRepository->getAnnonceInProgress($user->id);
            $annoncePause = $this->annonceRepository->getAnnoncePause($user->id);

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
