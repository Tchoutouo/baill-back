<?php

namespace App\Http\Controllers\API\Backend;
use App\Repositories\Backend\AdvertiserRepository;
use Exception;
use App\Models\User;
use App\Models\Profil;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdvertiserController extends \App\Http\Controllers\Controller
{
    //
    protected $advertiserRepository;

    public function __construct(AdvertiserRepository $advertiserRepository)
    {
        $this->advertiserRepository = $advertiserRepository;
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
                'username' => 'required|string|max:255', 
                'last_name' => 'required|string|max:255',
                'first_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email', // unique dans la table users
                'whatsapp_number' => 'required|min:9|max:9|unique:users,whatsapp_number', // 9
                'country' => 'required|string|max:255', 
                'city' => 'required|string|max:255', 
                'neighborhood' => 'required|string|max:255', 
                'password' => 'required|string|min:8'
            ],
            [
                'error' => 'Erreur...',
            ]
        );
            
            $inputs = $this->advertiserRepository->created($request->all());

            if($inputs)
            {
                return response()->json([
                    'success' => true,
                    'message' => 'Utilisateur enregistré avec success',
                    ]
                );
    
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non enregistré verifier vos donnée',
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
    public function update(Request $request, $advertiser)
    {

        try{
            $request -> validate([
                'username' => 'required|string|max:255', 
                'last_name' => 'required|string|max:255',
                // 'first_name' => 'required|string|max:255',
                'email' => 'required|email', // unique dans la table users
                'whatsapp_number' => 'required|min:9|max:9', // 9
                'country' => 'required|string|max:255', 
                'city' => 'required|string|max:255', 
                'neighborhood' => 'required|string|max:255', 
                // 'cni' => 'string|max:25',
                // 'picture' => 'string|max:255',
                // 'password' => 'required|string|min:8'
            ],
            [
                'error' => 'Erreur...',
            ]);

            $advUpdate = $this->advertiserRepository->updated($request->all(),$advertiser, $request);
            if($advUpdate){
                return response()->json([
                    'success' => true,
                    'data' => $advUpdate,
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
    public function show($id)
    {
        try{
            $advertiser = $this->advertiserRepository->getById($id);
    
            if($advertiser){
                return response()->json([
                    'messsage' => 'success',
                    'data' => $advertiser
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
            $advertiser = $this->advertiserRepository->getById($id);

            // Si utilisateur à un abonnement
            if($advertiser)
            {
                return response()->json([
                    'message' => 'Vous ne pouvez pas supprimé cet annonceur abonnement en cours',
                    ]
                );
    
            }else{
                $result = $this->advertiserRepository->destroy($id);
    
                if(isset($result))
                {
                    return response()->json([
                        'message' => 'Une erreur est survenu lors de la suppression',
                        ]
                    );
                    
    
                }else{
                    return response()->json([
                        'message' => 'Annonceur supprimé avec success',
                        ]
                    );
    
                }
            }
        }
        catch(Exception $e){
            return response()->json($e);
        }
    }
}
