<?php

namespace App\Http\Controllers\API\Backend;

use App\Repositories\Backend\CategorieRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class CategorieController extends \App\Http\Controllers\Controller
{
    //
    protected $categorieRepository;

    public function __construct(CategorieRepository $categorieRepository)
    {
        $this->categorieRepository = $categorieRepository;
    }

    /** index */
    public function index($pagination, $search = null)
    {
        try{
            $allCategorie = $this->categorieRepository->getAllCategories($pagination,$search);
            if(!empty($allCategorie)){
                return response()->json([
                    'success'=>true,
                    'data'=>$allCategorie,
                ]);
            }
            else{
                return response()->json([
                    'success'=>false,
                    'data'=>$allCategorie,
                ]);
            }
        }catch(Exception $e){
            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);
        }
    }

    /** index */
    public function ListingCategorie( $lang = null )
    {
        try{
            $allCategorie = $this->categorieRepository->getAllCategories(null, null, $lang);
            if(!empty($allCategorie)){
                return response()->json([
                    'success'=>true,
                    'data'=>$allCategorie,
                ]);
            }
            else{
                return response()->json([
                    'success'=>false,
                    'data'=>$allCategorie,
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
                'title' => 'required|string|max:255',
                // 'description' => 'required|string',
                // 'array_sous' => 'required|array',
            ],
            [
                'error' => 'Erreur...',
            ]
        );
                
            $inputs = $this->categorieRepository->created($request->all());
            
            if($inputs)
            {
                return response()->json([
                    'message' => 'Categorie enregistrÃ© avec success',
                    ]
                );
    
            }else{
                return response()->json([
                    'message' => 'Categorie non enregistrÃ© verifier vos donnÃ©e',
                    ]
                );
    
            }

        }catch(ValidationException $e){
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

    /**updated */
    public function update(Request $request, $categorie)
    {

        try{
            $request -> validate([
                'title' => 'required|string|max:255',
                // 'description' => 'required|string',
                // 'array_sous' => 'required|array',
            ],
            [
                'error' => 'Erreur...',
            ]);
            $result = $this->categorieRepository->updated($request->all(),$categorie);
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
        }catch(ValidationException $e){
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
    public function show($id)
    {
        try{
            $categorie = $this->categorieRepository->getById($id);
    
            if($categorie){
                return response()->json([]);
            }else{
                return response()->json([
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
            $categorie = $this->categorieRepository->getById($id);

            // Si Categorie existe
            if($categorie)
            {
                $result = $this->categorieRepository->destroy($id);
                // Suppression ok
                if(isset($result))
                {
                    return response()->json([
                        'success' => true,
                        'message' => 'categorie supprimÃ©e avec success',
                        ]
                    );
                
                //Si elle est associÃ©e Ã  une annonce pas de suppression
                }else{
                    return response()->json([
                        'success' => false,
                        'message' => 'Cette catÃ©gorie est attachÃ©e Ã  une annonce',
                        ]
                    );
    
                }
                
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Identifiant invalide',
                    ]
                );
            }
        }
        catch(Exception $e){
            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);
        }
    }


    //** Listes des annonces en fonction des catÃ©gories */
    public function categAnnonce($arrayCateg){
        
        try{
            $arrayCateg = json_decode($arrayCateg);
            $categorie = $this->categorieRepository->getAnnonceCateg($arrayCateg);
    
            if($categorie){
                return response()->json([
                    'success' => true,
                    'data' => $categorie,
                ]);
            }else{
                return response()->json([
                    'success' => false,
                    'data' => [],
                    ]
                );
            }
        }
        catch(Exception $e){
            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);

        }
    }
}


