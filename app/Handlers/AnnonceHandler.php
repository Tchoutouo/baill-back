<?php

namespace App\Handlers;

use Exception;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Repositories\Backend\AnnonceRepository;
use App\Http\Controllers\API\Backend\StripeControllers;
use App\Http\Controllers\API\Backend\PaymentController;
use Illuminate\Support\Facades\Auth;



class AnnonceHandler  {

    protected $annonceRepository;
    protected $stripeController;
    protected $paymentController;

    public  function __construct(AnnonceRepository $annonceRepository, StripeControllers $stripeController, PaymentController $paymentMobile){

        $this->annonceRepository = $annonceRepository;
        $this->stripeController = $stripeController;
        $this->paymentController = $paymentMobile;

    }

    public function storeAnnonce ($inputs, $dataPaiement = null){
        try {
            $result = DB::transaction( function() use ($inputs, $dataPaiement){
                
                // Attache avec les catégories
                
                
                $categorie = $inputs['categorie'];
                $annonce = $inputs['annonce'];
                for ($i=0; $i < count($categorie); $i++) { 
                    $annonce->categories()->attach($categorie[$i]);
                }
                
                if(isset($dataPaiement)){
                    $statusPaiement = null;
                    
                    if ($annonce['abonnement_id'] != '1') {
                        if ($dataPaiement['mode_paiement'] === "Stripe") {
                            $statusPaiement = $this->stripeController->stripePayment($dataPaiement, $annonce->id);
                        }
                        if ($dataPaiement['mode_paiement'] === "Mobile money") {
                            $statusPaiement = $this->paymentController->initiatePayment($dataPaiement, $annonce->id);
                            // dd($statusPaiement);

                        }
                    }
    
                    return $statusPaiement;
                }else{
                    return $annonce;
                }
            });
            DB::commit();

        }catch(Exception $e) {

            DB::rollBack();
            
        }

        if(isset($result)){

            return $result;

        }else{

        }

    }

    // Function update
    public function updated($inputs){
        try {
           

            $result = DB::transaction( function() use ($inputs){
                //dd($inputs['categorie']);
                $categorie = $inputs['categorie'];
                $annonce = $inputs['annonce'];
                for ($i=0; $i < count($categorie); $i++) { 
                    $annonce->categories()->sync($categorie[$i]);
                }
                return $annonce;
            });
            DB::commit();

        }catch(Exception $e) {

            DB::rollBack();
            
        }

        if(isset($result)){

            return $result;

        }else{

        }

    }

}