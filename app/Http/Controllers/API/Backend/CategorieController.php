<?php

namespace App\Http\Controllers\API\Backend;

use App\Repositories\Backend\CategorieRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
            return response()->json($e);
        }
    }

    /**store */
    public function store(Request $request)
    {
        try{
            $request -> validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
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
                    'message' => 'Categorie enregistré avec success',
                    ]
                );
    
            }else{
                return response()->json([
                    'message' => 'Categorie non enregistré verifier vos donnée',
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
    public function update(Request $request, $categorie)
    {

        try{
            $request -> validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                // 'array_sous' => 'required|array',
            ],
            [
                'error' => 'Erreur...',
            ]);
            $result = $this->categorieRepository->updated($request->all(),$categorie);
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
            return response()->json($e);

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
                        'message' => 'categorie supprimée avec success',
                        ]
                    );
                
                //Si elle est associée à une annonce pas de suppression
                }else{
                    return response()->json([
                        'success' => false,
                        'message' => 'Cette catégorie est attachée à une annonce',
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
            return response()->json($e);
        }
    }


    //** Listes des annonces en fonction des catégories */
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
            return response()->json($e);

        }
    }
}
