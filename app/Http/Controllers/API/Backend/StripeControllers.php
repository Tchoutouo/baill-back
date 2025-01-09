<?php

namespace App\Http\Controllers\API\Backend;
use Stripe\Stripe;
use Stripe\PaymentIntent;
// use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StripeControllers extends \App\Http\Controllers\Controller
{
    //

    public function checkout(Request $request)
    {

        // Configurer la clé API Stripe
        Stripe::setApiKey(config('services.stripe.secret'));
        
        try {
            // Créer une intention de paiement
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount * 100, // Montant en cents
                'currency' => 'usd',
                // 'payment_method' => 'pm_card_visa',
                // 'payment_method' => $request->payment_method,
                // 'confirmation_method' => 'manual',
                // 'confirm' => false,
                // 'return_url' => route('payment.completed'),
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never', // Empêche les redirections
                ],
            ]);

            return response()->json([
                'success' => true,
                'paymentIntent' => $paymentIntent->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
