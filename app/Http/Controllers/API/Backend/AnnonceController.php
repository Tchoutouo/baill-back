<?php

namespace App\Http\Controllers\API\Backend;

use App\Repositories\Backend\AnnonceRepository;
use App\Repositories\Backend\AbonnementRepository;
use App\Repositories\Backend\CategorieRepository;
use App\Handlers\AnnonceHandler;
use App\Http\Controllers\Api\Backend\PictureController;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Picture;


class AnnonceController extends \App\Http\Controllers\Controller
{
    //
    protected $annonceRepository;
    protected $annonceHandler;
    protected $pictureController;
    protected $abonnementController;
    protected $abonnementRepository;
    protected $categorieRepository;

    public function __construct(AnnonceRepository $annonceRepository, AnnonceHandler $annonceHandler, PictureController $pictureController, 
    CategorieRepository $categorieRepository, AbonnementRepository $abonnementRepository)
    {
        $this->annonceRepository = $annonceRepository;
        $this->annonceHandler = $annonceHandler;
        $this->pictureController = $pictureController;
        $this->abonnementRepository = $abonnementRepository;
        $this->categorieRepository = $categorieRepository;
    }

    /** index */
    public function create(Request $request)
    {
        try{
            $allAbonnement = $this->abonnementRepository->getAll();
            $allCategorie = $this->categorieRepository->getAll();
            return response()->json([
                'abonnement'=>$allAbonnement,
                'categorie'=>$allCategorie,
            ]);
        }catch(Exception $e){
            return response()->json($e);
        }
    }

    /** index */
    public function dashboard($user)
    {
        dd("fdfdf");
        try{
            
            return response()->json([]);
        }catch(Exception $e){
            return response()->json($e);
        }
    }

    /** index */
    public function index(Request $request)
    {
        
        try{
            
            return response()->json([]);
        }catch(Exception $e){
            return response()->json($e);
        }
    }

    /**store */
    public function store(Request $request)
    {

        try{
            $request -> validate([
                'title' => 'required|string|max:255', 
                'subtitle' => 'required|string|max:255', 
                'description' => 'required|string',
                'price' => 'required|numeric',
                'contact' => 'required|string|max:9', 
                'country' => 'required|string|max:255',
                'neighborhood' => 'required|string|max:255',//quartier
                // 'is_published' => 'required|integer|max:2',
                // 'status' => 'required|string|max:255',
                // 'picture' => 'required|array',
                // 'is_forward' => 'required|string|max:255',
                'categorie' => 'required|array',
                'abonnement_id' => 'required|string|max:255',
                'user_id' => 'required|string|max:255',
            ],
            [
                'error' => 'Erreur...',
            ]
        );
            $inputs = $this->annonceRepository->created($request->all());
            
            $inputs = $this->annonceHandler->store($inputs);

            $pictures = $this->pictureController->store($request, $inputs->id);

            if($pictures)
            {
                return response()->json([
                    'success' => true,
                    'message' => 'Annonce enregistré avec success',
                    ]
                );
    
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Annonce non enregistré verifier vos donnée',
                    ]
                );
    
            }

        }catch(Exception $e){
            return response()->json($e);

        }
    }

    /**updated */
    public function update(Request $request, $annonce)
    {

        try{
            $request -> validate([
                //validation
            ]);
            $result = $this->annonceRepository->updated($request->all(),$annonce);
            if($result){
                return response()->json([
                    'message' => 'Modification effectuée avec success',
                    ]
                );
            }else{
                return response()->json([
                    'message' => 'Echec de modification',
                    ]
                );
            }
        }catch(Exception $e){
            return response()->json($e);

        }
    }

    /**show */
    public function show($id)
    {
        try{
            $annonce = $this->annonceRepository->getById($id);
    
            if($annonce){
                return response()->json([
                    'message' => 'success ',
                    'data' => $annonce,
                ]);
            }else{
                return response()->json([
                    'message' => 'identifiant non valide ',
                    ]
                );
            }
        }
        catch(Exception $e){
            return response()->json($e);

        }
    }

    /**destroy */
    public function destroy(Request $request, $id)
    {
        try{
            $result = $this->annonceRepository->destroy($id);

            if(isset($result))
            {
                return response()->json([
                    'message' => 'Une erreur est survenu lors de la suppression',
                    ]
                );
                

            }else{
                return response()->json([
                    'message' => 'Annonce supprimé avec success',
                    ]
                );

            }
         
        }
        catch(Exception $e){
            return response()->json($e);
        }
    }
}
