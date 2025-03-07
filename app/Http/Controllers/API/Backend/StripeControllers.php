<?php

namespace App\Http\Controllers\API\Backend;
use Stripe\Stripe;
use Stripe\PaymentIntent;
// use Stripe\PaymentMethod;
use App\Repositories\Backend\PaiementRepository;
use App\Repositories\Backend\AnnonceRepository;
use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;
// use Illuminate\Support\Facades\Auth;

class StripeControllers extends \App\Http\Controllers\Controller
{
    //
    protected $paiementRepository;
    protected $annonceRepository;

    public function __construct(AnnonceRepository $annonceRepository, PaiementRepository $paiementRepository)
    {
        $this->paiementRepository = $paiementRepository;
        $this->annonceRepository = $annonceRepository;
    }

    public function stripePayment($dataPaiement, $annonce_id)
    {
        // Configurer la clé secrète Stripe
        Stripe::setApiKey(config('services.stripe.secret'));
        try {
            
            $paymentIntent = PaymentIntent::create([
                'amount' => $dataPaiement['amount']*100,
                'currency' => 'usd',
                'payment_method' => $dataPaiement['payment_method'],
                'confirm' => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never', // Désactive les paiements nécessitant une redirection
                ],
            ]);

            if ($paymentIntent->status == 'succeeded') {

                $data = [
                    "mode_paiement"=>$dataPaiement['mode_paiement'],
                    "montant"=>$dataPaiement['amount'],
                    "date_paiement"=>now(),
                    "number"=>$dataPaiement['number'],
                    // "customer"=>$dataPaiement['customer'],
                    "user_id"=> $dataPaiement['user_id'],
                    "abonnement_id"=>$dataPaiement['abonnement_id'],
                ];
                $storePaiement = $this->paiementRepository->created($data);

                if ($storePaiement) {// Update statut de l'annonce
                    $this->annonceRepository->changeStatusAnnonce($dataPaiement['user_id'], $annonce_id, 3);
                }

            }

            return [
                'success' => true,
                'status' => $paymentIntent->status,
                'client_secret' => $paymentIntent->client_secret,
            ];

        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'stripe_error' => $e->getStripeCode(),
            ];
        }
    }
}
