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
use App\Http\Controllers\API\Backend\PaymentController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
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
    protected $paymentController;

    public function __construct(AnnonceRepository $annonceRepository, AnnonceHandler $annonceHandler, PictureController $pictureController, 
    CategorieRepository $categorieRepository, AbonnementRepository $abonnementRepository, UserRepository $userRepository, 
    StripeControllers $stripeController, PaymentController $paymentMobile)
    {
        $this->annonceRepository = $annonceRepository;
        $this->annonceHandler = $annonceHandler;
        $this->pictureController = $pictureController;
        $this->abonnementRepository = $abonnementRepository;
        $this->categorieRepository = $categorieRepository;
        $this->userRepository = $userRepository;
        $this->stripeController = $stripeController;
        $this->paymentController = $paymentMobile;
        
    }

    /** Listing des annonces cotÃ© admin */
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
            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);
        }
    }

    /** Listing des annonces */
    public function index($user_id, $nbr_annonce, $search = null)
    {
        $authUser = Auth::user();

        // Un annonceur ne peut consulter que ses propres annonces
        if ((int) $authUser->id !== (int) $user_id) {
            return response()->json(['success' => false, 'message' => 'AccÃ¨s non autorisÃ©.'], 403);
        }

        try{
            $user = $this->userRepository->getById($authUser->id);
            $allAnnonce = $this->annonceRepository->getAllAnnonce($nbr_annonce, $authUser->id, $search);

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
            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);
        }
    }

    //Change status annonces
    public function changeStatus($user_id, $annonce_id, $new_status, $dataPaiement = null){
        try {

            $user = Auth::user();

            // VÃ©rifier que l'annonce appartient bien Ã  l'utilisateur connectÃ©
            $annonce = $this->annonceRepository->getById($annonce_id);
            if (!$annonce || (int) $annonce->user_id !== (int) $user->id) {
                return response()->json(['success' => false, 'message' => 'AccÃ¨s non autorisÃ©.'], 403);
            }

            if($new_status == 3 && $dataPaiement){
                $donnee = [
                    'validity_period' => $dataPaiement['validity_period']
                ];

                $this->annonceRepository->update($annonce_id,$donnee);
                
                $dataPaiement = json_decode($dataPaiement, true);
                $dataPaiement['user_id'] = $user->id;
                $dataPaiement['number'] = $user->whatsapp_number;
                $dataPaiement['customer'] = $user->username;
                $dataPaiement['date_paiement'] = Carbon::now()->format('Y-m-d');
                $dataPaiement['abonnement_id'] =  $this->annonceRepository->getById($annonce_id)->abonnement_id;
                if ($dataPaiement['mode_paiement'] === 'Stripe') {
                    $statutPaiement = $this->stripeController->stripePayment($dataPaiement, $annonce_id);
                }else{
                    $statutPaiement = $this->paymentController->initiatePayment($dataPaiement, $annonce_id);
                }
                
                if (isset($statutPaiement['reference'])) {//Cas paiement mobile
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'En attente de paiement...',
                    ]);
                }

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
                    $mailPaiement = $this->annonceRepository->mailPaiment(env('MAIL_USERNAME'),$title, $dataPaiement['amount'], $dataPaiement['mode_paiement'], $typeAbonnement);
                    if($mailPaiement){
                        return response()->json([
                            'success' => true,
                            'message' => 'Paiement rÃ©ussi... Mise Ã  jour effectuÃ©e avec success. Mail de confirmation envoyÃ© Ã  l\'administrateur',
                            // 'url' => route('dashboard',['id'=>$user_id]),
                        ]);
                    }else{
                        return response()->json([
                            'success' => true,
                            'message' => 'Paiement rÃ©ussi... Mise Ã  jour effectuÃ©e avec success. Erreur lors de l\'envoi du mail de confirmation',
                            // 'url' => route('dashboard',['id'=>$user_id]),
                        ]);
                    }
                }
            }
            
            $changeStatus = $this->annonceRepository->changeStatusAnnonce($user->id, $annonce_id, $new_status);

            if(isset($changeStatus)){
                return response()->json([
                    'success' => true,
                    'message' => 'Mise Ã  jour effectuÃ©e avec success',
                    // 'url' => route('dashboard',['id'=>$user_id]),
                ]);
            }
            else{
                return response()->json([
                    'success' => false,
                    'message' => 'Echec lors de la mise Ã  jour du status',
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
                'message' => 'Status changÃ© avec success',
                // 'url' => route('dashboard',['id'=>$user_id])
            ]);
        }
        else{
            return response()->json([
                'success' => false,
                'message' => 'Echec lors de la mise Ã  jour du status',
            ]);
        }
    }
    

    //Change abonnement annonce
    public function changeAbonnement($user_id, $annonce_id, $new_abonnement){

        $authId = Auth::id();

        // VÃ©rifier que l'annonce appartient bien Ã  l'utilisateur connectÃ©
        $annonce = $this->annonceRepository->getById($annonce_id);
        if (!$annonce || (int) $annonce->user_id !== (int) $authId) {
            return response()->json(['success' => false, 'message' => 'AccÃ¨s non autorisÃ©.'], 403);
        }

        $changeAbonnement = $this->annonceRepository->changeAbonnementAnnonce($authId, $annonce_id, $new_abonnement);

        if(isset($changeAbonnement)){
            return response()->json([
                'success' => true,
                'message' => 'Abonnement changÃ© avec success',
            ]);
        }
        else{
            return response()->json([
                'success' => false,
                'message' => 'Echec lors de la mise Ã  jour du status',
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
            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);
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
                    // user_id n'est pas acceptÃ© du client â€” forcÃ© via Auth::id()
                ],
                [
                'error' => 'Erreur...',
                ]
            );

            // user_id forcÃ© depuis le token â€” jamais depuis le corps de la requÃªte
            $safeData = array_merge($request->all(), ['user_id' => $user->id]);

            if($user->qte_free == 0 && $request->abonnement_id === "1"){

                $inputs = $this->annonceRepository->created($safeData);

                $this->pictureController->storePicture($request, $inputs['annonce']->id);

                if ($inputs) {
                    return response()->json([
                            'success' => true,
                            'message' => 'Annonce enregistrÃ©e mais pas publiÃ©e, quota d\' annonce free atteint.',
                        ]
                    );
                }else{
                    return response()->json([
                            'success' => false,
                            'message' => 'Echec d\'enregistrement, nombre d\'abonnement free atteint',
                        ]
                    );
                }
            }

            if($request->abonnement_id === "1"){

                $inputs = $this->annonceRepository->created($safeData);

                $this->pictureController->storePicture($request, $inputs['annonce']->id);

                $inputsAnnonce = $this->annonceHandler->storeAnnonce($inputs,null);

                if (isset($inputsAnnonce)) {
                    $free = $this->userRepository->decrementFree($user->id);
                    return response()->json([
                            'success' => true,
                            'message' => 'Annonce enregistrÃ©e et publiÃ©e',
                        ]
                    );
                }else{
                    return response()->json([
                            'success' => false,
                            'message' => 'Annonce enregistrÃ© avec statut encours. Mais attachÃ© Ã  aucune catÃ©gorie',
                        ]
                    );
                }
            }
            // dd($request->payment_datas);
            // $dataPaiement = json_decode($request->payment_datas, true);
            $dataPaiement = is_string($request->payment_datas) ? json_decode($request->payment_datas, true) : $request->payment_datas;
            // $dataPaiement = $request->payment_datas;

            $dataPaiement['user_id'] = $user->id;
            $dataPaiement['abonnement_id'] = $request->abonnement_id;
            $dataPaiement['number'] = $user->whatsapp_number;
            $dataPaiement['customer'] = $user->username;
            $dataPaiement['date_paiement'] = Carbon::now()->format('Y-m-d');

            $inputs = $this->annonceRepository->created($safeData);

            $this->pictureController->storePicture($request, $inputs['annonce']->id);

            $inputsAnnonce = $this->annonceHandler->storeAnnonce($inputs,$dataPaiement);


            if (isset($inputsAnnonce)) {
                if($inputsAnnonce['success'] === true)
                {
                    $typeAbonnement = $this->abonnementRepository->getById($request->abonnement_id)->name;
                    $mailPaiement = $this->annonceRepository->mailPaiment(env('MAIL_USERNAME'),$inputs['annonce']->title, $dataPaiement['amount'], $dataPaiement['mode_paiement'], $typeAbonnement);
                    if ($mailPaiement) {

                        return response()->json([
                            'success' => true,
                            'message' => 'Annonce enregistrÃ© et paiement rÃ©ussi. Votre administrateur a reÃ§u un message de confirmation',
                            ]
                        );

                    }else{
                        return response()->json([
                            'success' => true,
                            'message' => 'Annonce enregistrÃ© et paiement rÃ©ussi. Votre administrateur n\'a pas reÃ§u un message de confirmation',
                            ]
                        );
                    }
        
                }else{

                    if ($inputsAnnonce['status'] && $inputsAnnonce['status']==="PENDING") {
                        return response()->json([
                                'success' => true,
                                'verify' => true,
                                'message' => 'Annonce enregistrÃ© en attente de validation...',
                                'data' => $inputsAnnonce,
                            ]
                        );
                    }

                    return response()->json([
                            'success' => false,
                            'message' => 'Annonce enregistrÃ© mais echec de paiement',
                            'error-payment' => $inputsAnnonce,
                        ]
                    );
        
                }
            }else{
                $free = $this->userRepository->decrementFree($user->id);
                return response()->json([
                        'success' => true,
                        'status_free' => $free,
                        'message' => 'Annonce enregistrÃ© et paiement free, mais elle ne sera pas mise en avant',
                    ]
                );
            }
            

        }catch(ValidationException $e){

            \Log::error('Erreur lors de la crÃ©ation d\'une annonce: ' . $e->getMessage(), [
                'erreur_validation' => $e->validator->errors(),
            ]);
            // RÃ©cupÃ©rer les erreurs liÃ© Ã  la validation
            $errors = $e->validator->errors();

            // Retourner les erreurs en rÃ©ponse JSON ou autre objet
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
                    'message' => 'Modification effectuÃ©e avec success',
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
            \Log::error('Erreur lors de la mise a jour d\'une annonce: ' . $e->getMessage(), [
                'erreur_validation' => $e->validator->errors(),
            ]);
            // RÃ©cupÃ©rer les erreurs
            $errors = $e->validator->errors();

            // Retourner les erreurs en rÃ©ponse JSON ou autre objet
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $errors
            ], 422);
        }
    }

    /**show */
    public function get_annonce($annonce_id, $lang = null)
    {
        try{
            $annonce = $this->annonceRepository->getAnnonce($annonce_id, $lang);
            
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
            Log::error('Erreur lors de l\'affichage d\'une annonce: ' . $e->getMessage());

            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);
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
                    'message' => 'Annonce supprimÃ© avec success',
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

            \Log::error('Erreur lors de la suppression d\'une annonce: ' . $e->getMessage());

            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);
        }
    }
}

