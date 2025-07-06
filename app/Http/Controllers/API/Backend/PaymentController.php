<?php

namespace App\Http\Controllers\API\Backend;

use Illuminate\Http\Request;
use App\Repositories\Backend\PaiementRepository;
use App\Repositories\Backend\AnnonceRepository;
use App\Services\MobileMoneyService;
use App\Repositories\Backend\AbonnementRepository;
use Illuminate\Support\Facades\Auth;
use App\Events\CheckPaymentMobile;
use Illuminate\Support\Facades\Log;

class PaymentController extends \App\Http\Controllers\Controller
{

    protected $annonceRepository;
    protected $mobileMoney;
    protected $paiementRepository;
    protected $abonnementRepository;

    public function __construct(AnnonceRepository $annonceRepository, MobileMoneyService $mobileMoney, PaiementRepository $paiementRepository, AbonnementRepository $abonnementRepository)
    {
        $this->annonceRepository = $annonceRepository;
        $this->mobileMoney = $mobileMoney;
        $this->paiementRepository = $paiementRepository;
        $this->abonnementRepository = $abonnementRepository;
    }


    
    public function initiatePayment($dataPay, $annonce_id)
    {
        Log::info('Entrer donnée dataPayment',$dataPay);
        $dataPayment = is_string($dataPay) ? json_decode($dataPay, true) : $dataPay;

        try {
            $userId = Auth::user()->id;
            // dd($dataPayment);
            $storePaiement = $this->paiementRepository->created($dataPayment);
            $nameAnnonce = $this->annonceRepository->getById($annonce_id)->title;
            $response = $this->mobileMoney->initiatePayment($dataPayment, $annonce_id,$userId,$storePaiement->id,$nameAnnonce);
            // $paymentResponse = $this->mobileMoney->initiatePayment($dataPayment, $annonce_id,$userId,$storePaiement->id,$nameAnnonce);
            // $response = $paymentResponse->getData(true);


            if ($response) {
                $dataPayment["annonce_id"] = $annonce_id;
                $response["dataPayment"] = $dataPayment;
                $response = $this->checkPaiement($response, $dataPayment, $annonce_id);
        
                return $response;
            }

        } catch (\Exception $th) {
            Log::error('Erreur lors de l\'initialisation du paiement methode initiatePayment: ' . $th->getMessage());
            
            Log::info('erreur initialisation de paiement');
        }
    }

    public function checkPaiement($response, $dataPayment, $annonce_id){
        if ($response["status"]) {
            if ($response["status"] === "SUCCESSFULL") {

                $storePaiement = $this->paiementRepository->created($dataPayment);

                if ($storePaiement) {// Update statut de l'annonce
                    $this->annonceRepository->changeStatusAnnonce($dataPayment["user_id"], $annonce_id, 3);
                }
                $response["success"] = true;
            }
            $response["success"] = false;

            $allData = [
                "mode_paiement"=>$dataPayment["mode_paiement"],
                "amount"=>$dataPayment["amount"],
                "payer"=>$dataPayment["payer"],
                "user_id"=>$dataPayment["user_id"],
                "abonnement_id"=>$dataPayment["abonnement_id"],
                "number"=>$dataPayment["number"],
                "customer"=>$dataPayment["customer"],
                "token"=>$response["token"],
                "status"=>$response["status"],
                "reference"=>$response["reference"],
                "success"=>$response["success"],
                "annonce_id"=>$annonce_id
            ];

            return $allData;
        }

        return null;
    }

    public function callbackPayment(Request $request,$abonnementId, $annonceId,$userId,$paiementId)
    {
        try {
            Log::info('debut de la mise à jour apres le paiement');
            /** Déclancher l'evenement si statut du paiement a changé */
            $data = $request->all();
            // event(new CheckPaymentMobile($data));
            if ($data['status'] === 'SUCCESS') {
                $this->annonceRepository->changeStatusAnnonce($userId, $annonceId, 3);
                $typeAbonnement = $this->abonnementRepository->getById($abonnementId)->name;
                $nameAnnonce = $this->annonceRepository->getById($annonceId)->title;
                $this->paiementRepository->updated($paiementId,$data['reference'],2);
                $this->annonceRepository->mailPaiment(env('mail_username'),$nameAnnonce, $data['amount'],"Paiement mobile", $typeAbonnement);
            }else{
                $this->paiementRepository->updated($paiementId,$data['reference'],1);//Statut echec
            }

            Log::info('fin de la mise à jour apres le paiement');

        } catch (\Exception $th) {
            Log::error('Erreur lors du paiement methode callbackPayment: ' . $th->getMessage());
            
            Log::info('Erreur lors du paiement'. $th);
            return response()->json(['error' => $th]);
        }
    }
}

