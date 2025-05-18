<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class MobileMoneyService
{
    protected $appKey;
    protected $secretKey;
    protected $baseUrl;
    protected $token;
    protected $client;

    public function __construct()
    {
        $this->appKey = env('APPKEY_FREEMOPAY');
        $this->secretKey = env('SECRETKEY_FREEMOPAY');
        $this->token = env('TOKENMOMO_FREEMOPAY');
        $this->baseUrl = env('FREEMOPAY_BASE_URL');
        $this->client = new Client();
    }

    /**
     * Initier un paiement Mobile Money.
     *
     * @param string $phoneNumber
     * @param float $amount
     * @param string $transactionId
     * @return array
     */

     public function authFreemopay(): ?string
     {
         try {
             $response = $this->client->post("{$this->baseUrl}/api/v2/payment/token", [
                 'headers' => [
                     'Content-Type' => 'application/json',
                    //  'Authorization' => 'Bearer ' . $this->token,
                 ],
                 'json' => [
                     'appKey' => $this->appKey,
                     'secretKey' => $this->secretKey,
                 ],
                 'http_errors' => false
             ]);
     
             // Vérification de la réponse
             if ($response->getStatusCode() !== 200) {
                 return null;
             }
     
             $token_data = json_decode($response->getBody(), true);
     
             if (!isset($token_data['access_token'])) {
                 return null;
             }
     
             return $token_data['access_token'];
     
         } catch (RequestException $e) {
            Log::error('Erreur de connexion API de paiement', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Impossible de se connecter à l\'API distante.'
            ], 503);
         } 
     }

public function initiatePayment($data,$annonce_id,$userId,$paiementId)
{
    try {
        // Authentification
        $token = $this->authFreemopay();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur d\'authentification',
                ]
            );
        }
        
        // Préparation des données de paiement
        $paymentData = [
            "amount" => $data['amount'],
            "currency" => "XAF",
            "payer" => $data['payer'],
            "externalId" => $data['externalId'],
            "description" => "Annonce paiement",
            "callback" => "https://24f3-41-202-207-11.ngrok-free.app/api/webhook/freemopay/{$data['abonnement_id']}/{$annonce_id}/{$userId}/{$paiementId}",
        ];

        $response = $this->client->post("{$this->baseUrl}/api/v2/payment/", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
            'json' => $paymentData,
            'http_errors' => false // Pour gérer manuellement les erreurs
        ]);

        // Traitement de la réponse
        $statusCode = $response->getStatusCode();
        $responseBody = json_decode($response->getBody(), true);

        if ($statusCode !== 200) {
            return null;
        }

        $checkpayment = $this->checkTransactionStatus($token, $responseBody["reference"]);

        return $checkpayment;

    } catch (RequestException $e) {
        Log::error('Echec initialisation du paiement', [
            'message' => $e->getMessage(),
        ]);

        return response()->json([
            'error' => 'Echec initialisation du paiement'
        ], 503);
        
    }
}

    /**
     * Vérifier le statut d'une transaction.
     *
     * @param string $reference
     * @return array
     */
    public function checkTransactionStatus($token, $reference)
    {
        try {
            $response = $this->client->get("{$this->baseUrl}api/v2/payment/{$reference}", [ // Utilisez une propriété pour l'URL de paiement
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                'http_errors' => false // Pour gérer manuellement les erreurs
            ]);
            
            $responseBody = json_decode($response->getBody(), true);
            $responseBody["token"] = $token;

            return $responseBody;

        } catch (ClientException $e) {
            // Gérer l'erreur
            $response = $e->getResponse();
            $errorDetails = json_decode($response->getBody(), true);

            return response()->json([
                'error' => $errorDetails,
                'message' => 'Echec initialisation du paiement'
            ]);
        }
    }
}
