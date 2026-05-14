<?php

namespace App\Http\Controllers\API\Backend;

use App\Repositories\Backend\SousCategorieRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SousCategorieController extends \App\Http\Controllers\Controller
{
    //
    protected $sous_categorieRepository;

    public function __construct(SousCategorieRepository $sous_categorieRepository)
    {
        $this->sous_categorieRepository = $sous_categorieRepository;
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
        //dd($request);
        try{
            $request -> validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'array_cat' => 'required|array',
            ],
            [
                'error' => 'Erreur...',
            ]
        );
            
            $inputs = $this->sous_categorieRepository->created($request->all());
            //dd($inputs);
            if($inputs)
            {
                return response()->json([
                    'message' => 'Sous-categorie enregistrÃ© avec success',
                    ]
                );
            }else{
                return response()->json([
                    'message' => 'Sous-categorie non enregistrÃ© verifier vos donnÃ©es',
                    ]
                );
            }

        }catch(Exception $e){
            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);

        }
    }

    /**updated */
    public function update(Request $request, $sous_categorie)
    {

        try{
            $request -> validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'array_cat' => 'required|array',
            ],
            [
                'error' => 'Erreur...',
            ]);
            $result = $this->sous_categorieRepository->updated($request->all(),$sous_categorie);
            if($result){
                return response()->json([
                    'message' => 'Modification effectuÃ©e avec success',
                    ]
                );
            }else{
                return response()->json([
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
            $sous_categorie = $this->sous_categorieRepository->getById($id);
        
            if($sous_categorie){
                return response()->json([
                    'message' => 'success',
                    'data' => $sous_categorie
                ]);
            }else{
                return response()->json([
                    'message' => 'Identifiant non valide',
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
            $sous_categorie = $this->sous_categorieRepository->getById($id);

            // Si cette sous-categorie est associÃ©e Ã  une categorie
            $result = $this->sous_categorieRepository->destroy($id);
            //dd($result);
            if($sous_categorie)
            {
                return response()->json([
                    'message' => 'Vous ne pouvez pas supprimÃ© cet sous_categories abonnement en cours',
                    ]
                );
    
            }else{
                $result = $this->sous_categorieRepository->destroy($id);
    
                if(isset($result))
                {
                    return response()->json([
                        'message' => 'Une erreur est survenu lors de la suppression',
                        ]
                    );
                    
    
                }else{
                    return response()->json([
                        'message' => 'categorie supprimÃ© avec success',
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
}


