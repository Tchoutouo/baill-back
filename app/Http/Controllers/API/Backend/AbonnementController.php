<?php

namespace App\Http\Controllers\API\Backend;
use App\Repositories\Backend\AbonnementRepository;
use Exception;
use App\Models\User;
use App\Models\Profil;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


class AbonnementController extends \App\Http\Controllers\Controller
{
    //
    protected $abonnementRepository;

    public function __construct(AbonnementRepository $abonnementRepository)
    {
        $this->abonnementRepository = $abonnementRepository;
    }

    /** index */
    public function index($nbre_page, $search = null)
    {
        try{
            $allAbonnement = $this->abonnementRepository->getAllAbonnement($nbre_page,$search);
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
                'time' => 'required|integer',
                'type_time' => 'required|string|max:1',
                'price' => 'required|integer',
                'type' => 'required|string|max:255',
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

        }catch(ValidationException $e){
            // Récupérer les erreurs
            $errors = $e->validator->errors();

            // Retourner les erreurs en réponse JSON ou autre objet
            return response()->json([
                'success' => false,
                'message' => 'Erreur d\' enregistrement',
                'errors' => $errors
            ], 422);
        }
    }

    /**updated */
    public function update(Request $request, $abonnement)
    {

        try{
            $request -> validate([
                'name' => 'required|string|max:255',
                'time' => 'required|integer',
                'type_time' => 'required|string|max:1',
                'price' => 'required|integer',
                'type' => 'required|string|max:255',
            ],
            [
                'error' => 'Erreur...',
            ]
        );

            $result = $this->abonnementRepository->updated($request->all(),$abonnement);
            if($result){
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
                    'success' => true,
                    'data' => $abonnement
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

    public function statusAbonnement($id){
        // dd("ffff");
        $changeStatus = $this->abonnementRepository->changeStatusAbonnement($id);

        if(isset($changeStatus)){
            return response()->json([
                'success' => true,
                'message' => 'Status changé avec success'
            ]);
        }
        else{
            return response()->json([
                'success' => false,
                'message' => 'Echec lors de la mise à jour du status',
            ]);
        }
    }
}
