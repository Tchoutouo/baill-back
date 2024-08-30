<?php

namespace App\Http\Controllers;

use App\Repositories\Backend\CategorieRepository;
use Exception;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    //
    protected $categorieRepository;

    public function __construct(CategorieRepository $categorieRepository)
    {
        $this->categorieRepository = $categorieRepository;
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
            
            $inputs = $this->categorieRepository->created($request->all());
            
            if($inputs)
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
    public function update(Request $request, $categorie)
    {

        try{
            $request -> validate([
                //validation
            ]);
            $result = $this->categorieRepository->updated($request->all(),$categorie);
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

            // Si utilisateur à un abonnement
            if($categorie)
            {
                return response()->json([
                    'message' => 'Vous ne pouvez pas supprimé cet categories abonnement en cours',
                    ]
                );
    
            }else{
                $result = $this->categorieRepository->destroy($id);
    
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
