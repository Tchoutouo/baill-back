<?php

namespace App\Http\Controllers\API\Backend;

use App\Repositories\Backend\SousCategorieRepository;
use Exception;
use Illuminate\Http\Request;

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
            return response()->json($e);
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
                    'message' => 'Sous-categorie enregistré avec success',
                    ]
                );
            }else{
                return response()->json([
                    'message' => 'Sous-categorie non enregistré verifier vos données',
                    ]
                );
            }

        }catch(Exception $e){
            return response()->json($e);

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
            return response()->json($e);

        }
    }

    /**destroy */
    public function destroy(Request $request, $id)
    {
        try{
            $sous_categorie = $this->sous_categorieRepository->getById($id);

            // Si cette sous-categorie est associée à une categorie
            $result = $this->sous_categorieRepository->destroy($id);
            //dd($result);
            if($sous_categorie)
            {
                return response()->json([
                    'message' => 'Vous ne pouvez pas supprimé cet sous_categories abonnement en cours',
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
                        'message' => 'categorie supprimé avec success',
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
