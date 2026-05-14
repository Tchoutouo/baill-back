<?php

namespace App\Http\Controllers\API\Backend;

use App\Repositories\Backend\ModePaiementRepository;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);
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
            
            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);
        }
    }


    //** Change status */
    public function status(Request $request){

        $om = filter_var($request->input('om'), FILTER_VALIDATE_BOOLEAN);
        $momo = filter_var($request->input('momo'), FILTER_VALIDATE_BOOLEAN);
        $stripe = filter_var($request->input('stripe'), FILTER_VALIDATE_BOOLEAN);

        $arrayModePayment = ['om' => $om, 'momo' => $momo, 'stripe' => $stripe];

        try{
            $result = $this->modepaiementRepository->changeStatus($arrayModePayment);
            
            if(isset($result) && !empty($result)){
                return response()->json([
                    "success"=>true,
                    "data"=>"Status changÃ© avec success"
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

            Log::error('Erreur inattendue dans ' . class_basename($this) . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);
        }
    }
}


