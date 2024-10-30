<?php

namespace App\Http\Controllers\API\Frontend;

use Illuminate\Http\Request;
use App\Repositories\Backend\CategorieRepository;
use App\Repositories\Backend\AnnonceRepository;
use App\Http\Controllers\Api\Backend\PictureController;
use Exception;

class DashboardController extends \App\Http\Controllers\Controller
{
    //
    protected $annonceRepository;
    protected $annonceHandler;
    protected $pictureController;
    protected $abonnementController;
    protected $abonnementRepository;
    protected $categorieRepository;
    protected $userRepository;

    public function __construct(AnnonceRepository $annonceRepository, PictureController $pictureController, CategorieRepository $categorieRepository)
    {
        $this->annonceRepository = $annonceRepository;
        $this->pictureController = $pictureController;
        $this->categorieRepository = $categorieRepository;
    }

    function dashboard(){
        $allAnnonce = $this->annonceRepository->getAllAnnonceFront();
        $annonceUne = $this->annonceRepository->getAnnonceUne();
        $allCategorie = $this->categorieRepository->getAll();
        if(isset($allCategorie)){
            return response()->json([
                'success'=> true,
                'data_annonce'=> $allAnnonce,
                'data_annonce_une'=> $annonceUne,
                'data_categorie'=> $allCategorie,
            ]);
        }else{
            return response()->json([
                'success'=> false,
            ]);
        }
    }

    /** Trie homepage */
    public function trie(Request $request)
    {
        try{
            $categ = $request->query('categ');
            $country = $request->query('country');
            $city = $request->query('city');
            $allAnnonce = $this->annonceRepository->getTrieAnnonce($categ, $country, $city);
            
            if(isset($allAnnonce)){
                return response()->json([
                    'success' => true,
                    'annonces' => $allAnnonce,
                ]);
            }
            else{
                return response()->json([
                    'success' => false,
                    'annonces' => $allAnnonce,
                ]);
            }
        }catch(Exception $e){
            return response()->json($e);
        }
    }
}
