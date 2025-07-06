<?php

namespace App\Http\Controllers\API\Frontend;

use Illuminate\Http\Request;
use App\Repositories\Backend\CategorieRepository;
use App\Repositories\Backend\AnnonceRepository;
use App\Http\Controllers\API\Backend\PictureController;
use App\Mail\ContactFormMail;
use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;

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

    function dashboard($user_id = null){
        try{
            $allAnnonce = $this->annonceRepository->getAllAnnonceFront($user_id);
            $annonceUne = $this->annonceRepository->getAnnonceUne($user_id);
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
            
        }catch(Exception $e){
            \Log::error('Une erreur est survenue lors de la récupération des données de la page d\'accueil: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des données de la page d\'accueil.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /** Trie homepage */
    public function trie(Request $request)
    {
        try{
            // dd($request->all());
            $allAnnonce = $this->annonceRepository->getTrieAnnonce($request->categ, $request->country, $request->city, $request->user_id);
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
            \Log::error('Une erreur est survenue lors du tri des annonces: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du tri des annonces.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

        /**Send email de contact */
    /*public function contact(Request $request)
    {
        try{
            $request -> validate([
                'name' => 'required|string|max:255',
                // 'phone' => 'required|string',
                'email' => 'required|email|max:255',
                'objet' => 'required|string|max:255',
                'message' => 'required|string',
            ],
            [
                'error' => 'Erreur...',
            ]
        );
            // Envoyer l'email
            try {
                Mail::to(env('mail_username'))->send(new ContactFormMail($request->name,$request->email,$request->phone, $request->message, $request->objet));
                $statutEmail = true;
            } catch (\Exception $e) {
                $statutEmail = false;
            }
            
            if($statutEmail)
            {
                return response()->json([
                    'success' => true,
                    'message' => 'Mail envoyé avec success',
                    ]
                );
    
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Echec d\'envoi du mail',
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
    }*/
    public function contact(Request $request)
    {
        try {
            // Validate request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'objet' => 'required|string|max:255',
                'message' => 'required|string',
                'phone' => 'nullable|string|max:20', // Added phone as nullable
            ], [
                'name.required' => 'Le nom est requis.',
                'email.required' => 'L\'email est requis.',
                'email.email' => 'L\'email doit être valide.',
                'objet.required' => 'L\'objet est requis.',
                'message.required' => 'Le message est requis.',
                'phone.max' => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
            ]);

            // Send email
            try {
                Mail::to(config('mail.from.address'))->send(new ContactFormMail(
                    $validated['name'],
                    $validated['email'],
                    $validated['phone'] ?? '', // Handle nullable phone
                    $validated['message'],
                    $validated['objet']
                ));

                return response()->json([
                    'success' => true,
                    'message' => 'Message envoyé avec succès.',
                ], 200);
            } catch (\Exception $e) {
                // Log the error for debugging
                \Log::error('Échec de l\'envoi de l\'email: ' . $e->getMessage(), [
                    'email' => $validated['email'],
                    'objet' => $validated['objet'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Échec de l\'envoi du message. Veuillez réessayer plus tard.',
                ], 500);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation des données.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Catch any other unexpected errors
            \Log::critical('Erreur inattendue dans contact: ' . $e->getMessage(), [
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer.',
            ], 500);
        }
    }
}
