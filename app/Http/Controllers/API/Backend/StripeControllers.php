<?php

namespace App\Http\Controllers\API\Backend;;
use Stripe\Stripe;
use Stripe\PaymentIntent;
// use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StripeControllers extends \App\Http\Controllers\Controller
{
    //

    public function checkout(Request $request)
    {
        // dd("bien");
        // Configurer la clé API Stripe
        Stripe::setApiKey(config('services.stripe.secret'));
        // dd("coolll",$request->all());
        try {
            // Créer une intention de paiement
            $paymentIntent = PaymentIntent::create([
                'amount' => 50 * 100, // Montant en cents
                // 'amount' => $request->amount * 100, // Montant en cents
                'currency' => 'usd',
                'payment_method' => 'pm_card_visa',
                // 'payment_method' => $request->payment_method,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'return_url' => route('payment.completed'),
            ]);

            return response()->json([
                'success' => true,
                'paymentIntent' => $paymentIntent,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
