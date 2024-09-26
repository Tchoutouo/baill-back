<?php

namespace App\Http\Controllers\API\Backend;

use App\Repositories\Backend\AnnonceRepository;
use App\Handlers\AnnonceHandler;
use Exception;
use Illuminate\Http\Request;

class AnnonceController extends \App\Http\Controllers\Controller
{
    //
    protected $annonceRepository;
    protected $annonceHandler;

    public function __construct(AnnonceRepository $annonceRepository, AnnonceHandler $annonceHandler)
    {
        $this->annonceRepository = $annonceRepository;
        $this->annonceHandler = $annonceHandler;
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
        // dd("sdfsd");
        try{
            $request -> validate([
                'title' => 'required|string|max:255', 
                'subtitle' => 'required|string|max:255', 
                'description' => 'required|string',
                'price' => 'required|numeric',
                'contact' => 'required|string|max:9', 
                'country' => 'required|string|max:255',
                'neighborhood' => 'required|string|max:255',//quartier
                'is_published' => 'required|integer|max:2',
                'is_published' => 'required|integer|max:2',
                'status' => 'required|string|max:255',
                'is_forward' => 'required|string|max:255',
                'categorie_id' => 'required|string|max:255',
                'abonnement_id' => 'required|string|max:255',
                'user_id' => 'required|string|max:255',
            ],
            [
                'error' => 'Erreur...',
            ]
        );
            //dd($request->all());
            $inputs = $this->annonceRepository->created($request->all());
            //dd("sdfsdf");
            $inputs = $this->annonceHandler->store($inputs);

            if($inputs)
            {
                return response()->json([
                    'message' => 'Annonceur enregistré avec success',
                    ]
                );
    
            }else{
                return response()->json([
                    'message' => 'Annonceur non enregistré verifier vos donnée',
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
                return response()->json([
                    'message' => 'success ',
                    'data' => $annonce,
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
        catch(Exception $e){
            return response()->json($e);
        }
    }
}
