<?php

namespace App\Http\Controllers\API\Backend;

use App\Repositories\Backend\ModePaiementRepository;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;

class ModePaiementController extends \App\Http\Controllers\Controller
{
    //
    protected $modepaiementRepository;

    public function __construct(ModePaiementRepository $modepaiementRepository)
    {
        $this->modepaiementRepository = $modepaiementRepository;
    }

    public function index(){

        try{
            $modepaiment  = $this->modepaiementRepository->getAll(null);
            if(isset($modepaiment)){
                if (!empty($modepaiment)) {
                    return response()->json([
                        'success' => true,
                        'data' => $modepaiment,
                    ]);
                }else{
                    return response()->json([
                        'success' => true,
                        'data' => [],
                    ]);
                }
            }
            else{
                return response()->json([
                    'success' => false,
                    'data' => null,
                ]);
            }
        }catch(Exception $e){
            return response()->json($e);
        }
    }

    //** Listing cote advertiser */
    public function indexAdvert(){

        try{
            $modepaiment  = $this->modepaiementRepository->getAll(1);
            if(isset($modepaiment)){
                if (!empty($modepaiment)) {
                    return response()->json([
                        'success' => true,
                        'data' => $modepaiment,
                    ]);
                }else{
                    return response()->json([
                        'success' => true,
                        'data' => [],
                    ]);
                }
            }
            else{
                return response()->json([
                    'success' => false,
                    'data' => null,
                ]);
            }
        }catch(Exception $e){
            \Log::error('Erreur lors du changement des modes de paiement: ' . $e->getMessage());
            
            return response()->json($e);
        }
    }


    //** Change status */
    public function status($arrayModePayment){

        $arrayModePayment = json_decode($arrayModePayment, true);
        
        try{
            $result = $this->modepaiementRepository->changeStatus($arrayModePayment);
            
            if(isset($result) && !empty($result)){
                return response()->json([
                    "success"=>true,
                    "data"=>"Status changé avec success"
                ]);
            }else{
                return response()->json([
                    "success"=>false,
                    "message"=>"Mode de paiement non valide..."
                ]);
            }

        }catch(Exception $e){
            \Log::error('Erreur lors du changement des status des modes de paiement: ' . $e->getMessage(), [
                'data_paiement' => $arrayModePayment,
            ]);

            return response()->json($e);
        }
    }
}
