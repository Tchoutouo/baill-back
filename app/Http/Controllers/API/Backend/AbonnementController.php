<?php

namespace App\Http\Controllers\API\Backend;
use App\Repositories\Backend\AbonnementRepository;
use Exception;
use App\Models\User;
use App\Models\Profil;
use Illuminate\Http\Request;

class AbonnementController extends \App\Http\Controllers\Controller
{
    //
    protected $abonnementRepository;

    public function __construct(AbonnementRepository $abonnementRepository)
    {
        $this->abonnementRepository = $abonnementRepository;
    }

    /** index */
    public function index(Request $request)
    {
        try{
            $allAbonnement = $this->abonnementRepository->getAll();
            if(!empty($allAbonnement)){
                return response()->json([
                    'success'=>true,
                    'data'=>$allAbonnement,
                ]);
            }
            else{
                return response()->json([
                    'success'=>false,
                    'data'=>$allAbonnement,
                ]);
            }
        }catch(Exception $e){
            return response()->json($e);
        }
    }

    /**store */
    public function store(Request $request)
    {
        try{
            $request -> validate([
                'name' => 'required|string|max:255', 
                'description' => 'required|string|max:255', 
                'time' => 'required|integer',
                'price' => 'required|double',
                'type' => 'required|string|max:255', 
                'is_actived' => 'required|string|max:255',
            ],
            [
                'error' => 'Erreur...',
            ]
        );
            
            $inputs = $this->abonnementRepository->created($request->all());

            if($inputs)
            {
                return response()->json([
                    'success' => true,
                    'message' => 'Abonnement enregistré avec success',
                    ]
                );
    
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Abonnement non enregistré verifier vos donnée',
                    ]
                );
    
            }

        }catch(Exception $e){
            return response()->json($e);

        }
    }

    /**updated */
    public function update(Request $request, $abonnement)
    {

        try{
            $request -> validate([
                'name' => 'required|string|max:255', 
                'description' => 'required|string',
                'time' => 'required|string|max:255',
                'price' => 'required|double',
                'type' => 'required|string|max:255', 
                'is_actived' => 'required|string|max:255',
            ],
            [
                'error' => 'Erreur...',
            ]
        );

            $result = $this->abonnementRepository->updated($request->all(),$abonnement);
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
            $abonnement = $this->abonnementRepository->getById($id);
    
            if($abonnement){
                return response()->json([
                    'messsage' => 'success',
                    'data' => $abonnement
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
            $abonnement = $this->abonnementRepository->getById($id);

            // Si utilisateur à un abonnement
            if($abonnement)
            {
                return response()->json([
                    'message' => 'Vous ne pouvez pas supprimé cet abonnement en cours',
                    ]
                );
    
            }else{
                $result = $this->abonnementRepository->destroy($id);
    
                if(isset($result))
                {
                    return response()->json([
                        'message' => 'Une erreur est survenu lors de la suppression',
                        ]
                    );
                    
                }else{
                    return response()->json([
                        'message' => 'Abonnement supprimé avec success',
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
