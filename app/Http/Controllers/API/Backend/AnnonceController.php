<?php

namespace App\Http\Controllers\API\Backend;

use App\Repositories\Backend\AnnonceRepository;
use App\Repositories\Backend\AbonnementRepository;
use App\Repositories\Backend\CategorieRepository;
use App\Repositories\Backend\UserRepository;
use App\Handlers\AnnonceHandler;
use App\Http\Controllers\API\Backend\PictureController;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

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
    protected $stripeController;

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

    /** Listing des annonces coté admin */
    public function getAllAnnonce($nbr_annonce, $search = null)
    {
        try{
            $allAnnonce = $this->annonceRepository->getAllAnnonce(null, $nbr_annonce, $search);
            
            if(isset($allAnnonce)){
                return response()->json([
                    'success' => true,
                    'annonces' => $allAnnonce,
                ]);
            }
            else{
                return response()->json([
                    'success' => false,
                    'annonces' => [],
                ]);
            }
        }catch(Exception $e){
            return response()->json($e);
        }
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
                    'annonces' => [],
                ]);
            }
        }catch(Exception $e){
            return response()->json($e);
        }
    }

    //Change status annonces
    public function changeStatus($user_id, $annonce_id, $new_status, $statusPayment = null){

        if(isset($statusPayment) && $statusPayment == false){
            return response()->json([
                'success' => false,
                'message' => 'Oups!!! Echec de paiement statut non changé',
                'url' => route('dashboard',['id'=>$user_id])
            ]);
        }
        $changeStatus = $this->annonceRepository->changeStatusAnnonce($user_id, $annonce_id, $new_status, $statusPayment);

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

    //Change status annonces par l'administrateur
    public function changeStatusAdmin($annonce_id, $new_status){
        
        $changeStatus = $this->annonceRepository->changeStatusAnnonce(null, $annonce_id, $new_status);

        if(isset($changeStatus)){
            return response()->json([
                'success' => true,
                'message' => 'Status changé avec success',
                // 'url' => route('dashboard',['id'=>$user_id])
            ]);
        }
        else{
            return response()->json([
                'success' => false,
                'message' => 'Echec lors de la mise à jour du status',
            ]);
        }
    }
    

    //Change abonnement annonce
    public function changeAbonnement($user_id, $annonce_id, $new_abonnement){
        
        $changeAbonnement = $this->annonceRepository->changeAbonnementAnnonce($user_id, $annonce_id, $new_abonnement);

        if(isset($changeAbonnement)){
            return response()->json([
                'success' => true,
                'message' => 'Abonnement changé avec success',
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

        $user = Auth::user();
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

            if($user->qte_free == 0 && $request->abonnement_id === "1"){
                return response()->json([
                        'success' => false,
                        'message' => 'Echec d\'enregistrement, nombre d\'abonnement free atteint',
                    ]
                );
            }

            $dataPaiement = $request->dataPaiement;

            $dataPaiement['user_id'] = $user->id;
            $dataPaiement['abonnement_id'] = $request->abonnement_id;
            $dataPaiement['number'] = $user->whatsapp_number;
            $dataPaiement['customer'] = $user->username;

            $inputs = $this->annonceRepository->created($request->all());

            $this->pictureController->storePicture($request, $inputs['annonce']->id);
            
            $inputsAnnonce = $this->annonceHandler->storeAnnonce($inputs,$dataPaiement);

            if (isset($inputsAnnonce)) {
                if($inputsAnnonce['success'] === true)
                {
                    return response()->json([
                        'success' => true,
                        'message' => 'Annonce enregistré et paiement réussi',
                        ]
                    );
        
                }else{
                    return response()->json([
                            'success' => false,
                            'message' => 'Annonce enregistré mais echec de paiement',
                            'error-payment' => $inputsAnnonce,
                        ]
                    );
        
                }
            }else{
                $free = $this->userRepository->decrementFree($user->id);
                return response()->json([
                        'success' => true,
                        'status_free' => $free,
                        'message' => 'Annonce enregistré et paiement free, mais elle ne sera pas mise en avant',
                    ]
                );
            }
            

        }catch(ValidationException $e){
            // Récupérer les erreurs lié à la validation
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

    /**show */
    public function get_annonce($annonce_id)
    {
        try{
            $annonce = $this->annonceRepository->getAnnonce($annonce_id);
            
            if($annonce){
                return response()->json([
                    'success' => true,
                    'data' => $annonce,
                ]);
            }else{
                return response()->json([
                    'success' => false,
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
    public function destroy($id, $arrayCategorie)
    {
        $arrayCategorie = json_decode($arrayCategorie);
        
        try{
            $result = $this->annonceRepository->deleteAnnonce($id,$arrayCategorie);
            if(isset($result))
            {
                return response()->json([
                    'success' => true,
                    'message' => 'Annonce supprimé avec success',
                    ]
                );

            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur est survenu lors de la suppression',
                    ]
                );

            }
         
        }
        catch(Exception $e){
            return response()->json($e);
        }
    }
}
