<?php

namespace App\Http\Controllers\API\Backend;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Repositories\Backend\PaiementRepository;
use App\Repositories\Backend\AnnonceRepository;

class PaymentController extends \App\Http\Controllers\Controller
{

    protected $paiementRepository;
    protected $annonceRepository;

    public function __construct(PaiementRepository $paiementRepository, AnnonceRepository $annonceRepository)
    {
        $this->paiementRepository = $paiementRepository;
        $this->annonceRepository = $annonceRepository;
    }

    // Confirmer le paiement le paiement.
     
    public function confirmPayment($payment_intent_id, $payment_method_id, $number_whatsapp, $user_id, $annonce_id, $abonnement_id)
    {
        // Configurer la clé secrète Stripe
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            // Récupérer l'identifiant du PaymentIntent et du PaymentMethod
            $paymentIntentId = $payment_intent_id;
            $paymentMethodId = $payment_method_id;

            // Associer le PaymentMethod au PaymentIntent
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            $paymentIntent->confirm([
                'payment_method' => $paymentMethodId,
            ]);

            // Checked le status après confirmation du paiement
            if ($paymentIntent->status === 'succeeded') {

                // Save paiement
                $data = [
                    "mode_paiement"=>"card",
                    "montant"=>$paymentIntent->amount,
                    "date_paiement"=>now(),
                    "number"=>$number_whatsapp,
                    "user_id"=>$user_id,
                    "abonnement_id"=>$abonnement_id,
                ];

                $result = $this->paiementRepository->created($data);

                // Si table paiement a été bien rempli faire la mise à jour de l'annonce payer
                if(isset($result)){

                    $this->annonceRepository->changeStatusAnnonce($user_id, $annonce_id,3);

                    return response()->json([
                        'success' => true,
                        'message' => 'Paiement réussi ! Votre annonce a été publiée',
                    ]);
                }
            } elseif ($paymentIntent->status === 'requires_action') { // cas des doubles authentification
                return response()->json([
                    'status' => 'requires_action',
                    'message' => 'Une authentification supplémentaire est requise.',
                    'payment_intent_client_secret' => $paymentIntent->client_secret,
                ]);
            }

            return response()->json([
                'status' => 'pending',
                'message' => 'Le paiement est en attente.',
            ]);
        } catch (\Exception $e) {
            // Gérer les erreurs Stripe
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

