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
        Log::info('Entrer donnée dataPayment', (array) $dataPay);
        $dataPayment = is_string($dataPay) ? json_decode($dataPay, true) : $dataPay;

        try {
            $userId = Auth::user()->id;

            // Créer le paiement une seule fois — avant l'appel au provider
            $storePaiement = $this->paiementRepository->created($dataPayment);
            $nameAnnonce   = $this->annonceRepository->getById($annonce_id)->title;

            $response = $this->mobileMoney->initiatePayment(
                $dataPayment, $annonce_id, $userId, $storePaiement->id, $nameAnnonce
            );

            if ($response) {
                $dataPayment['annonce_id'] = $annonce_id;
                // Passer l'ID du paiement déjà créé pour mise à jour (pas recréation)
                return $this->checkPaiement($response, $dataPayment, $annonce_id, $storePaiement->id);
            }

        } catch (\Exception $th) {
            Log::error('Erreur lors de l\'initialisation du paiement methode initiatePayment: ' . $th->getMessage());
        }

        return null;
    }

    public function checkPaiement($response, $dataPayment, $annonce_id, $paiementId = null)
    {
        if (empty($response['status'])) {
            return null;
        }

        if ($response['status'] === 'SUCCESSFULL') {
            // Activer l'annonce
            $this->annonceRepository->changeStatusAnnonce($dataPayment['user_id'], $annonce_id, 3);
            // Mettre à jour le statut du paiement existant (pas de double création)
            if ($paiementId) {
                $this->paiementRepository->updated($paiementId, $response['reference'] ?? null, 2);
            }
            $success = true;
        } else {
            $success = false;
        }

        return [
            'mode_paiement' => $dataPayment['mode_paiement'],
            'amount'        => $dataPayment['amount'],
            'payer'         => $dataPayment['payer'] ?? null,
            'user_id'       => $dataPayment['user_id'],
            'abonnement_id' => $dataPayment['abonnement_id'],
            'number'        => $dataPayment['number'],
            'customer'      => $dataPayment['customer'] ?? null,
            'token'         => $response['token'] ?? null,
            'status'        => $response['status'],
            'reference'     => $response['reference'] ?? null,
            'success'       => $success,
            'annonce_id'    => $annonce_id,
        ];
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
                $this->annonceRepository->mailPaiment(config('mail.from.address'),$nameAnnonce, $data['amount'],"Paiement mobile", $typeAbonnement);
            }else{
                $this->paiementRepository->updated($paiementId,$data['reference'],1);//Statut echec
            }

            Log::info('fin de la mise à jour apres le paiement');

        } catch (\Exception $th) {
            Log::error('Erreur lors du paiement methode callbackPayment: ' . $th->getMessage(), [
                'abonnement_id' => $abonnementId,
                'annonce_id'    => $annonceId,
                'paiement_id'   => $paiementId,
            ]);
            return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors du traitement du paiement.'], 500);
        }
    }
}

