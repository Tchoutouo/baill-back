<?php

namespace App\Http\Controllers\API\Backend;

use Illuminate\Http\Request;
use App\Repositories\Backend\PaiementRepository;
use App\Repositories\Backend\AnnonceRepository;
use App\Services\MobileMoneyService;
use App\Repositories\Backend\AbonnementRepository;
use Illuminate\Support\Facades\Auth;

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


    
    public function initiatePayment($dataPayment, $annonce_id)
    {
        try {
            $response = $this->mobileMoney->initiatePayment($dataPayment);
        
            $dataPayment["annonce_id"] = $annonce_id;
    
            $response["dataPayment"] = $dataPayment;
            
            $response = $this->checkPaiement($response, $dataPayment, $annonce_id);
    
            return $response;
        } catch (\Exception $th) {
            dd("bien",$th);
        }
    }

    public function checkPaymentStatus($dataPayment)
    {
        try {
            $dataPayment = json_decode($dataPayment,true);

            $response = $this->mobileMoney->checkTransactionStatus($dataPayment['token'], $dataPayment['reference']);
    
            $response = $this->checkPaiement($response, $dataPayment, $dataPayment["annonce_id"]);
    
            if ($response['status'] && $response['status']==="FAILED") {
                return response()->json([
                        'success' => false,
                        'message' => 'Annonce enregistré mais echec de paiement',
                    ]
                );
            }
    
            if ($response['status'] && $response['status']==="PENDING") {
                return response()->json([
                        'success' => false,
                        'verify' => true,
                        'message' => 'Annonce enregistré en attente de validation...',
                        'data' => $response,
                    ]
                );
            }
    
            $typeAbonnement = $this->abonnementRepository->getById($response['abonnement_id'])->name;
            $nameAnnonce = $this->annonceRepository->getById($response['annonce_id'])->title;
            $mailPaiement = $this->annonceRepository->mailPaiment(env('mail_username'),$nameAnnonce, $response['amount'], $response['mode_paiement'], $typeAbonnement);
    
            if ($mailPaiement) {
    
                return response()->json([
                    'success' => true,
                    'message' => 'Annonce enregistré et paiement réussi. Votre administrateur a reçu un message de confirmation',
                    ]
                );
            }

            return response()->json([
                'success' => false,
                'message' => 'Erreur de connexion...',
                ]);

        } catch (\Exception $e) {
            dd("error",$e);
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
}

