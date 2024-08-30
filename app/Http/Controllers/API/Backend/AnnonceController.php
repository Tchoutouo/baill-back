<?php

namespace App\Http\Controllers;

use App\Repositories\Backend\AnnonceRepository;
use Exception;
use Illuminate\Http\Request;

class AnnonceController extends Controller
{
    //
    protected $annonceRepository;

    public function __construct(AnnonceRepository $annonceRepository)
    {
        $this->annonceRepository = $annonceRepository;
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
            
            $inputs = $this->annonceRepository->created($request->all());
            
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
    public function update(Request $request, $annonce)
    {

        try{
            $request -> validate([
                //validation
            ]);
            $result = $this->annonceRepository->updated($request->all(),$annonce);
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
            $annonce = $this->annonceRepository->getById($id);
    
            if($annonce){
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
            $annonce = $this->annonceRepository->getById($id);

            // Si utilisateur à un abonnement
            if($annonce)
            {
                return response()->json([
                    'message' => 'Vous ne pouvez pas supprimé cet annonceur abonnement en cours',
                    ]
                );
    
            }else{
                $result = $this->annonceRepository->destroy($id);
    
                if(isset($result))
                {
                    return response()->json([
                        'message' => 'Une erreur est survenu lors de la suppression',
                        ]
                    );
                    
    
                }else{
                    return response()->json([
                        'message' => 'Annonce supprimé avec success',
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
