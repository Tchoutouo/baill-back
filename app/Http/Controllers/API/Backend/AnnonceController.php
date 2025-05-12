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
use App\Http\Controllers\API\Backend\StripeControllers;
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
    CategorieRepository $categorieRepository, AbonnementRepository $abonnementRepository, UserRepository $userRepository, StripeControllers $stripeController)
    {
        $this->annonceRepository = $annonceRepository;
        $this->annonceHandler = $annonceHandler;
        $this->pictureController = $pictureController;
        $this->abonnementRepository = $abonnementRepository;
        $this->categorieRepository = $categorieRepository;
        $this->userRepository = $userRepository;
        $this->stripeController = $stripeController;
        
    }

    /** Listing des annonces coté admin */
    public function getAllAnnonce($nbr_annonce, $search = null)
    {
        try{
            //$allAnnonce = $this->annonceRepository->getAllAnnonce(null, $nbr_annonce, $search);
            $allAnnonce = $this->annonceRepository->getAllAnnonce($nbr_annonce, null,  $search);

            
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
            //$allAnnonce = $this->annonceRepository->getAllAnnonce($user->id, $nbr_annonce, $search);
            $allAnnonce = $this->annonceRepository->getAllAnnonce($nbr_annonce, $user->id,  $search);
            
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
    public function changeStatus($user_id, $annonce_id, $new_status, $dataPaiement = null){
        try {

            $user = Auth::user();

            if($new_status == 3){
                $dataPaiement = json_decode($dataPaiement, true);
                $dataPaiement['user_id'] = $user->id;
                $dataPaiement['number'] = $user->whatsapp_number;
                $dataPaiement['abonnement_id'] =  $this->annonceRepository->getById($annonce_id)->abonnement_id;
                $statutPaiement = $this->stripeController->stripePayment($dataPaiement, $annonce_id);
                
                if ($statutPaiement['success'] == false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Echec paiement...',
                        'error' => $statutPaiement,
                        // 'url' => route('dashboard',['id'=>$user_id])
                    ]);
                }
                else{
                    $idAbonnement = $this->annonceRepository->getById($annonce_id)->abonnement_id;
                    $title = $this->annonceRepository->getById($annonce_id)->title;
                    $typeAbonnement = $this->abonnementRepository->getById($idAbonnement)->name;
                    $mailPaiement = $this->annonceRepository->mailPaiment(env('mail_username'),$title, $dataPaiement['amount'], $dataPaiement['mode_paiement'], $typeAbonnement);
                    if($mailPaiement){
                        return response()->json([
                            'success' => true,
                            'message' => 'Paiement réussi... Mise à jour effectuée avec success. Mail de confirmation envoyé à l\'administrateur',
                            // 'url' => route('dashboard',['id'=>$user_id]),
                        ]);
                    }else{
                        return response()->json([
                            'success' => true,
                            'message' => 'Paiement réussi... Mise à jour effectuée avec success. Erreur lors de l\'envoi du mail de confirmation',
                            // 'url' => route('dashboard',['id'=>$user_id]),
                        ]);
                    }
                }
            }
            
            $changeStatus = $this->annonceRepository->changeStatusAnnonce($user_id, $annonce_id, $new_status);

            if(isset($changeStatus)){
                return response()->json([
                    'success' => true,
                    'message' => 'Mise à jour effectuée avec success',
                    // 'url' => route('dashboard',['id'=>$user_id]),
                ]);
            }
            else{
                return response()->json([
                    'success' => false,
                    'message' => 'Echec lors de la mise à jour du status',
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e,
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

            if(!isset($request->payment_datas)){
                
                $inputs = $this->annonceRepository->created($request->all());

                $this->pictureController->storePicture($request, $inputs['annonce']->id);

                $inputsAnnonce = $this->annonceHandler->storeAnnonce($inputs,null);

                if (isset($inputsAnnonce)) {
                    return response()->json([
                            'success' => true,
                            'message' => 'Annonce enregistrée avec statut en cours. Pensez à la publiée',
                        ]
                    );
                }else{
                    return response()->json([
                            'success' => false,
                            'message' => 'Annonce enregistré avec statut encours. Mais attaché à aucune catégorie',
                        ]
                    );
                }
            }
            if($user->qte_free == 0 && $request->abonnement_id === "1"){
                return response()->json([
                        'success' => false,
                        'message' => 'Echec d\'enregistrement, nombre d\'abonnement free atteint',
                    ]
                );
            }
            dd($request->payment_datas);
            $dataPaiement = json_decode($request->payment_datas, true);
            // $dataPaiement = $request->payment_datas;

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
                    $typeAbonnement = $this->abonnementRepository->getById($request->abonnement_id)->name;
                    $mailPaiement = $this->annonceRepository->mailPaiment(env('mail_username'),$inputs['annonce']->title, $dataPaiement['amount'], $dataPaiement['mode_paiement'], $typeAbonnement);
                    if ($mailPaiement) {

                        return response()->json([
                            'success' => true,
                            'message' => 'Annonce enregistré et paiement réussi. Votre administrateur a reçu un message de confirmation',
                            ]
                        );

                    }else{
                        return response()->json([
                            'success' => true,
                            'message' => 'Annonce enregistré et paiement réussi. Votre administrateur n\'a pas reçu un message de confirmation',
                            ]
                        );
                    }
        
                }else{

                    if ($inputsAnnonce['status'] && $inputsAnnonce['status']==="PENDING") {
                        return response()->json([
                                'success' => false,
                                'verify' => true,
                                'message' => 'Annonce enregistré en attente de validation...',
                                'data' => $inputsAnnonce,
                            ]
                        );
                    }

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

            //$result = $this->annonceRepository->updated($request->all(),$annonce);
            $result = $this->annonceRepository->updated($annonce, $request->all());
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
