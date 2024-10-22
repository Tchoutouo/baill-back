<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
        /** Dashbord d'un annonceur */
    // public function dashboard($user_id)
    // {
    //     try{
    //         $user = $this->userRepository->getById($user_id);
    //         $allAnnonce = $this->annonceRepository->getAllAnnonce($user->id);
    //         $annoncePublisher = $this->annonceRepository->getAnnoncePublisher($user->id);
    //         $annonceExpired = $this->annonceRepository->getAnnonceExpired($user->id);
    //         $annonceInProgress = $this->annonceRepository->getAnnonceInProgress($user->id);
    //         $annoncePause = $this->annonceRepository->getAnnoncePause($user->id);
    //         // dd($allAnnonce);
    //         if(isset($allAnnonce)){
    //             return response()->json([
    //                 'success' => true,
    //                 'user' => $user,
    //                 'annonces' => $allAnnonce,
    //                 'annonce_qte_publisher' => $annoncePublisher,
    //                 'annonce_qte_expired' => $annonceExpired,
    //                 'annonce_qte_inprogress' => $annonceInProgress,
    //                 'annonce_qte_pause' => $annoncePause,
    //             ]);
    //         }
    //         else{
    //             return response()->json([
    //                 'success' => false,
    //                 'user' => $user,
    //                 'annonces' => $allAnnonce,
    //             ]);
    //         }
    //     }catch(Exception $e){
    //         return response()->json($e);
    //     }
    // }
}
