<?php

namespace App\Http\Controllers\API\Backend;
use App\Repositories\Backend\UserRepository;
use Exception;
use App\Models\User;
use App\Models\Profil;
use Illuminate\Http\Request;

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
            return response()->json($e);
        }
    }

    /**store */
    public function store(Request $request)
    {
        try{
            $request -> validate([

                // 'last_name' => 'required',
    
            ]);
            
            $inputs = $this->userRepository->created($request->all());
            
            $result = $this->learnerHandler->store($inputs);
            if($result)
            {
                return response()->json([
                    'message' => 'Utilisateur enregistré avec success',
                    ]
                );
    
            }else{
                return response()->json([
                    'message' => 'Utilisateur non enregistré verifier vos donnée',
                    ]
                );
    
            }

        }catch(Exception $e){
            return response()->json($e);

        }
    }

    /**updated */
    public function update(Request $request, $user)
    {

        try{
            $request -> validate([
                //validation
            ]);
            $result = $this->userRepository->updated($request->all(),$learner);
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
            $user = $this->userRepository->getById($id);
    
            if($user){
                return response()->json([]);
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
            $userabonnement = $this->userRepository->getById($id);

            // Si utilisateur à un abonnement
            if($userabonnement)
            {
                return response()->json([
                    'message' => 'Vous ne pouvez pas supprimé cet annonceur abonnement en cours',
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
                        'message' => 'Utilisateur supprimé avec success',
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
