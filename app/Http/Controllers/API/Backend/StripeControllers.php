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

    public function stripePayment(Request $request)
    {
        // Configurer la clé secrète Stripe
        Stripe::setApiKey(config('services.stripe.secret'));
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount,
                'currency' => 'usd',
                'payment_method' => $request->payment_method,
                'confirm' => true,
                // 'return_url' => 'https://votre-site.com/success',
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never', // Désactive les paiements nécessitant une redirection
                ],
            ]);
            
            if ($paymentIntent->status == 'succeeded') {
                // Enregistrer le paiement

                $data = [
                    "mode_paiement"=>"stripe",
                    "montant"=>$request->amount,
                    "date_paiement"=>now(),
                    "number"=>$request->whatsapp_number,
                    "user_id"=> $request->user_id,
                    "abonnement_id"=>$request->abonnement_id,
                ];

                $this->paiementRepository->created($data);
            }

            return response()->json([
                'success' => true,
                'status' => $paymentIntent->status,
                'client_secret' => $paymentIntent->client_secret,
            ]);

        } catch (ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'stripe_error' => $e->getStripeCode(),
            ], 500);
        }
    }
}
