<?php

namespace App\Http\Controllers\API\Backend;
use App\Repositories\Backend\UserRepository;
use Exception;
use App\Models\User;
use App\Models\Profil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;

class UserController extends \App\Http\Controllers\Controller
{
    //
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /** index */
    public function index(Request $request)
    {
        try{
            return response()->json([]);
        }catch(Exception $e){
            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);
        }
    }

    /**store */
    public function store(Request $request)
    {
        try{
            $request -> validate([
                'username' => 'required|string|max:255', 
                'last_name' => 'required|string|max:255', 
                'email' => 'required|email|unique:users,email', // unique dans la table users
                'whatsapp_number' => 'required|min:9|max:9', // 9
                'country' => 'required|string|max:255', 
                'city' => 'required|string|max:255', 
                'neighborhood' => 'required|string|max:255', 
                'password' => 'required|string|min:8'
            ],
            [
                'error' => 'Erreur...',
            ]);
            
            $inputs = $this->userRepository->created($request->all());
            if($inputs)
            {
                return response()->json([
                    'success' => true,
                    'message' => 'Utilisateur enregistrÃ© avec success',
                    ]
                );
    
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non enregistrÃ© verifier vos donnÃ©e',
                    ]
                );
    
            }

        }catch(Exception $e){
            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);

        }
    }

    /**updated */
    public function update(Request $request, $user)
    {

        try{
            $request -> validate([
                //validation
            ]);
            $result = $this->userRepository->updated($request->all(),$user);
            if($result){
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
        }catch(Exception $e){
            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);

        }
    }

    /**show */
    public function show($id)
    {
        try{
            $user = $this->userRepository->getById($id);
    
            if($user){
                return response()->json([
                    'success' => true,
                    'data' => $user
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
            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);

        }
    }

    /**destroy */
    public function destroy(Request $request, $id)
    {
        try{
            $userabonnement = $this->userRepository->getById($id);

            // Si utilisateur Ã  un abonnement
            if($userabonnement)
            {
                return response()->json([
                    'message' => 'Vous ne pouvez pas supprimÃ© cet annonceur abonnement en cours',
                    ]
                );
    
            }else{
                $result = $this->userRepository->destroy($id);
    
                if(isset($result))
                {
                    return response()->json([
                        'message' => 'Une erreur est survenu lors de la suppression',
                        ]
                    );
                    
    
                }else{
                    return response()->json([
                        'message' => 'Utilisateur supprimÃ© avec success',
                        ]
                    );
    
                }
            }
        }
        catch(Exception $e){
            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);
        }
    }

    public function forgetPassword(Request $request)
    {
        try {

            // dd('status', $request->all());
            $request -> validate([
                'email' => 'required|email|exists:users,email',
            ]);
    
            $status = Password::sendResetLink($request->only('email'));

            if($status === Password::RESET_LINK_SENT){
                return response()->json([
                        'success' => true,
                        'message' => 'Lien de rÃ©initialisation envoyÃ© avec success',
                    ]
                );
            }else{
                return response()->json([
                        'success' => false,
                        'message' => "Email inexistant dans le systÃ¨me...",
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error('Error lors mise Ã  jour du password : ' . $e->getMessage());
            dd($e);
        }

    }


    public function newPassword(Request $request)
    {
        try {
            $request -> validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => ['required','confirmed', Rules\Password::defaults()], //Verifier si password === password_confirm
            ]);

            $status = Password::reset(
                $request->only('email, password, password_confirmation, token'),
                function($user, $password){
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->save();
                }
            );


        } catch (\Exception $e) {
            Log::error('Error lors mise Ã  jour du nouveau password : ' . $e->getMessage());
        }

    }


}

