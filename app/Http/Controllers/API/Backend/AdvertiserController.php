<?php

namespace App\Http\Controllers\API\Backend;
use App\Repositories\Backend\AdvertiserRepository;
use Exception;
use App\Models\User;
use App\Models\Profil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
    public function index($paginate , $search = null)
    {
        try{
            $allAdvertiser = $this->advertiserRepository->getAlluser($paginate,$search);
            
            if(isset($allAdvertiser) && !empty($allAdvertiser)){
                return response()->json([
                    "success"=>true,
                    "data"=>$allAdvertiser
                ]);
            }else{
                return response()->json([
                    "success"=>false,
                    "message"=>"Aucune donnée trouvée..."
                ]);
            }

        }catch(Exception $e){
            return response()->json($e);
        }
    }

    /**store */
    public function store(Request $request)
    {
        try {
            // Validation avec messages personnalisés
            $validatedData = $request->validate([
                'username' => 'required|string|max:255|unique:users,username',
                'last_name' => 'required|string|max:255',
                'first_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'whatsapp_number' => 'required|string|min:9|max:20|unique:users,whatsapp_number',
                'country' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'neighborhood' => 'required|string|max:255',
                // 'password' => [
                //     'required',
                //     'string',
                //     'min:8',
                //     'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
                // ]
            ], [
                'username.required' => 'Le nom d\'utilisateur est requis.',
                'username.unique' => 'Ce nom d\'utilisateur est déjà utilisé.',
                'last_name.required' => 'Le nom de famille est requis.',
                'first_name.required' => 'Le prénom est requis.',
                'email.required' => 'L\'adresse email est requise.',
                'email.email' => 'L\'adresse email doit être valide.',
                'email.unique' => 'Cette adresse email est déjà utilisée.',
                'whatsapp_number.required' => 'Le numéro WhatsApp est requis.',
                'whatsapp_number.unique' => 'Ce numéro WhatsApp est déjà utilisé.',
                'whatsapp_number.min' => 'Le numéro WhatsApp doit contenir au moins 9 chiffres.',
                'whatsapp_number.max' => 'Le numéro WhatsApp ne peut pas dépasser 20 chiffres.',
                'country.required' => 'Le pays est requis.',
                'city.required' => 'La ville est requise.',
                'neighborhood.required' => 'Le quartier est requis.',
                // 'password.required' => 'Le mot de passe est requis.',
                // 'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
                // 'password.regex' => 'Le mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spécial.',
            ]);

            // Hasher le mot de passe
            // $validatedData['password'] = Hash::make($validatedData['password']);

            // Créer l'utilisateur
            $user = $this->advertiserRepository->created($validatedData);

            if ($user) {
                if($user !== 1){
                    return response()->json([
                        'success' => true,
                        'message' => 'Utilisateur enregistré avec succès.',
                        'data' => [
                            'user_id' => $user->id,
                            'username' => $user->username,
                            'email' => $user->email
                        ]
                    ], 201);
                }else{
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur lors de l\'enregistrement de l\'utilisateur echec d\'envoi email.',
                    ], 500);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'enregistrement de l\'utilisateur.',
                ], 500);
            }

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation.',
                'errors' => $e->validator->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement d\'un utilisateur: ' . $e->getMessage());
            
            return response()->json([
                'success' => $e->getMessage(),
                'message' => 'Une erreur inattendue est survenue.',
            ], 500);
        }
    }

    /**updated */
    public function update(Request $request, $advertiser)
    {
        
        try{
            $request -> validate([
                'username' => 'required|string|max:255', 
                'last_name' => 'required|string|max:255',
                'first_name' => 'required|string|max:255',
                'email' => 'required|email', // unique dans la table users
                'whatsapp_number' => 'required|min:9|max:15', // 9
                'country' => 'required|string|max:255', 
                'city' => 'required|string|max:255', 
                'neighborhood' => 'required|string|max:255', 
                // 'cni' => 'string|max:25',
                // 'picture' => 'string',
                // 'password' => 'required|string|min:8'
            ],
            [
                'error' => 'Erreur...',
            ]);
            $datas = $request->all();
            if ($request->hasFile('picture')) {
                $user_img = $request->file('picture');
                $datas['picture'] = $user_img;
            }
            $advUpdate = $this->advertiserRepository->handleUpdate($advertiser, $datas);
            
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
                    'success' => true,
                    'data' => $advertiser
                ]);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'identifiant non valide '
                ]);
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


    //** Change status */
    public function status(Request $request, $id){
        
        try{
            $result = $this->advertiserRepository->changeStatus($id);
            
            if(isset($result) && !empty($result)){
                return response()->json([
                    "success"=>true,
                    "data"=>"Status changé avec success"
                ]);
            }else{
                return response()->json([
                    "success"=>false,
                    "message"=>"Identifiant non valide..."
                ]);
            }

        }catch(Exception $e){
            return response()->json($e);
        }
    }

    //** Update password */
    public function updatePassword(Request $request, $advertiser){
        
        try{
            $request -> validate([
                'password' => 'required|string|min:8',
                'new_password' => 'required|string|min:8',
                'confirm_password' => 'required|string|min:8',
            ]);

            $datas = $request->all();

            if($datas['new_password'] !== $datas['confirm_password']){
                    return response()->json([
                        'success' => false,
                        'message' => 'le nouveau de passe est different de la confirmation',
                        ]
                    );
            }

            $updatePass = $this->advertiserRepository->updatePassword($advertiser, $datas);
            if(isset($updatePass)){
                if ($updatePass === true) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Modification effectuée avec success',
                        ]
                    );
                }else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mot de passe actuel invalide',
                        ]
                    );
                }
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Identifiant non valide...',
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

    //** forget password */
    public function forgetPassword(Request $request, $advertiser){
        
        try{
            $request -> validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $datas = $request->all();

            $forgetPass = $this->advertiserRepository->forgetPassword($advertiser, $datas);
            if(isset($forgetPass)){
                
                if ($forgetPass === true) { //Si le mail a été envoyé
                    return response()->json([
                        'success' => true,
                        'message' => 'Réinitialisation effectuée avec success ',
                        ]
                    );
                }else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Echec d\'envoi du mail',
                        ]
                    );
                }
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Identifiant non valide...',
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

    public function checkEmailExists(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $exists = User::where('email', $request->email)->exists();
        return response()->json(['exists' => $exists], 200);
    }

    public function checkPhoneExists(Request $request)
    {
        $request->validate(['whatsapp_number' => 'required']);
        $exists = User::where('whatsapp_number', $request->whatsapp_number)->exists();
        return response()->json(['exists' => $exists], 200);
    }

    public function checkUsernameExists(Request $request)
    {
        $username = $request->input('username');
        $exists = User::where('username', $username)->exists();
        return response()->json(['exists' => $exists], 200);
    }

    public function checkPasswordExists($id,$password)
    {
        $exists = User::where('id', $id)->exists();
        if($exists){
            if (Hash::check($password, $exists->password)) {
                return response()->json([
                    'success' => true,
                    'message' => "password actuel okay"
                ]);
            }
        }
        return response()->json([
            'success' => false,
            'message' => "password actuel invalid"
        ]);
    }
}
