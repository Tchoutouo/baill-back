<?php

namespace App\Http\Controllers\API\Backend;

use App\Repositories\Backend\AnnonceRepository;
use App\Repositories\Backend\AbonnementRepository;
use App\Repositories\Backend\CategorieRepository;
use App\Repositories\Backend\UserRepository;
use App\Handlers\AnnonceHandler;
use App\Http\Controllers\Api\Backend\PictureController;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;


class AnnonceController extends \App\Http\Controllers\Controller
{
    //
    protected $annonceRepository;
    protected $annonceHandler;
    protected $pictureController;
    protected $abonnementController;
    protected $abonnementRepository;
    protected $categorieRepository;
    protected $userRepository;

    public function __construct(AnnonceRepository $annonceRepository, AnnonceHandler $annonceHandler, PictureController $pictureController, 
    CategorieRepository $categorieRepository, AbonnementRepository $abonnementRepository, UserRepository $userRepository)
    {
        $this->annonceRepository = $annonceRepository;
        $this->annonceHandler = $annonceHandler;
        $this->pictureController = $pictureController;
        $this->abonnementRepository = $abonnementRepository;
        $this->categorieRepository = $categorieRepository;
        $this->userRepository = $userRepository;
    }

    /** Listing des annonces */
    public function index($user_id, $nbr_annonce, $search = null)
    {
        try{
            $user = $this->userRepository->getById($user_id);
            $allAnnonce = $this->annonceRepository->getAllAnnonce($user->id, $nbr_annonce, $search);
            
            if(isset($allAnnonce)){
                return response()->json([
                    'success' => true,
                    'user' => $user,
                    'annonces' => $allAnnonce,
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

    //Change status annonces
    public function changeStatus($user_id, $annonce_id, $new_status){
        $changeStatus = $this->annonceRepository->changeStatusAnnonce($user_id, $annonce_id, $new_status);

        if(isset($changeStatus)){
            return response()->json([
                'success' => true,
                'message' => 'Status changé avec success',
                'url' => route('dashboard',['id'=>$user_id])
            ]);
        }
        else{
            return response()->json([
                'success' => false,
                'message' => 'Echec lors de la mise à jour du status',
            ]);
        }
    }
    
    /** create annonce */
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


    /**store */
    public function store(Request $request)
    {
        
        
        try{
            $request -> validate([
                'title' => 'required|string|max:255', 
                'description' => 'required|string',
                'price' => 'required|numeric',
                'country' => 'required|string|max:255',
                'categorie' => 'required|array',
                'abonnement_id' => 'required|string|max:255',
                'user_id' => 'required|string|max:255',
            ],
            [
                'error' => 'Erreur...',
            ]
        );
            $inputs = $this->annonceRepository->created($request->all());
           
            $inputsAnnonce = $this->annonceHandler->storeAnnonce($inputs);

            $pictures = $this->pictureController->storePicture($request, $inputsAnnonce->id);

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

        }catch(ValidationException $e){
            // Récupérer les erreurs
            $errors = $e->validator->errors();

            // Retourner les erreurs en réponse JSON ou autre objet
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $errors
            ], 422);
        }
    }

    /**updated */
    public function update(Request $request, $annonce)
    {

        try{
            $request -> validate([
                'title' => 'required|string|max:255', 
                'description' => 'required|string',
                'price' => 'required|numeric',
                'country' => 'required|string|max:255',
                'categorie' => 'required|array',
                'abonnement_id' => 'required|string|max:255',
                'user_id' => 'required|string|max:255',
            ],
            [
                'error' => 'Erreur...',
            ]);

            $result = $this->annonceRepository->updated($request->all(),$annonce);
            $inputsAnnonce = $this->annonceHandler->updated($result);

            $pictures = $this->pictureController->updated($request, $inputsAnnonce->id);

            if($pictures){
                return response()->json([
                    'success' => true,
                    'message' => 'Modification effectuée avec success',
                    ]
                );
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Echec de modification',
                    ]
                );
            }
        }catch(ValidationException $e){
            // Récupérer les erreurs
            $errors = $e->validator->errors();

            // Retourner les erreurs en réponse JSON ou autre objet
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $errors
            ], 422);
        }
    }

    /** Détail annonce */
    public function detail($id){
        dd($id);   
    }

    /**show */
    public function shows($annonce_id)
    {
        dd("bonjour");
        try{
            $annonce = $this->annonceRepository->getAnnonce($annonce_id);
            
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
