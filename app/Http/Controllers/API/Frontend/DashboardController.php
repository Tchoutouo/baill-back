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
            $allAnnonce = $this->annonceRepository->getTrieAnnonce($request->categ, $request->country, $request->city);
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

        /**Send email de contact */
    public function contact(Request $request)
    {
        try{
            $request -> validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string',
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
    }
}
