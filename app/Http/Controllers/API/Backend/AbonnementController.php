<?php

namespace App\Http\Controllers\API\Backend;
use App\Repositories\Backend\AbonnementRepository;
use Exception;
use App\Models\User;
use App\Models\Profil;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;


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
            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);
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
                // 'type' => 'required|string|max:255',
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
                    'message' => 'Abonnement enregistrÃ© avec success',
                    ]
                );
    
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Abonnement non enregistrÃ© verifier vos donnÃ©e',
                    ]
                );
    
            }

        }catch(ValidationException $e){
            // RÃ©cupÃ©rer les erreurs
            $errors = $e->validator->errors();

            // Retourner les erreurs en rÃ©ponse JSON ou autre objet
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
                // 'type' => 'required|string|max:255',
                ],
                [
                    'error' => 'Erreur...',
                ]
            );

            $result = $this->abonnementRepository->updated($request->all(),$abonnement);
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
            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);

        }
    }

    /**destroy */
    public function destroy(Request $request, $id)
    {
        try {
            $abonnement = \App\Models\Abonnement::find($id);

            if (!$abonnement) {
                return response()->json(["success" => false, "message" => "Abonnement introuvable."], 404);
            }

            $inUse = \App\Models\Annonce::where("abonnement_id", $id)->exists();
            if ($inUse) {
                return response()->json([
                    "success" => false,
                    "message" => "Impossible de supprimer : cet abonnement est utilise par des annonces.",
                ], 422);
            }

            $abonnement->delete();

            return response()->json(["success" => true, "message" => "Abonnement supprime avec succes."]);

        } catch (Exception $e) {
            Log::error("Erreur inattendue dans " . class_basename($this) . ": " . $e->getMessage());
            return response()->json(["success" => false, "message" => "Une erreur inattendue est survenue."], 500);
        }
    }

        public function statusAbonnement($id){
     
        $changeStatus = $this->abonnementRepository->changeStatusAbonnement($id);

        if(isset($changeStatus)){
            return response()->json([
                'success' => true,
                'message' => 'Status changÃ© avec success'
            ]);
        }
        else{
            return response()->json([
                'success' => false,
                'message' => 'Echec lors de la mise Ã  jour du status',
            ]);
        }
    }
}


