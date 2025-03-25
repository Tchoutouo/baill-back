<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class MobileMoneyService
{
    protected $username;
    protected $password;
    protected $baseUrl;
    protected $token;
    protected $client;

    public function __construct()
    {
        $this->username = env('USERNAMEMOMO_CAMPAY');
        $this->password = env('PASSWORDMOMO_CAMPAY');
        $this->token = env('TOKENMOMO_CAMPAY');
        $this->baseUrl = env('CAMPAY_BASE_URL');
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

     public function authCampay(): ?string
     {
         try {
             $response = $this->client->post("{$this->baseUrl}token/", [
                 'headers' => [
                     'Content-Type' => 'application/json',
                     'Authorization' => 'Bearer ' . $this->token,
                 ],
                 'json' => [
                     'username' => $this->username,
                     'password' => $this->password,
                 ],
                 'http_errors' => false
             ]);
     
             // Vérification de la réponse
             if ($response->getStatusCode() !== 200) {
                 return null;
             }
     
             $token_data = json_decode($response->getBody(), true);
     
             if (!isset($token_data['token'])) {
                 return null;
             }
     
             return $token_data['token'];
     
         } catch (RequestException $e) {
            dd($e);
         } catch (\Exception $e) {
             dd($e);
         }
     }

public function initiatePayment($data)
{
    try {
        // Authentification
        $token = $this->authCampay();
        
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
            "from" => $data['from'],
            "description" => "Annonce paiement"
        ];

        $response = $this->client->post("{$this->baseUrl}collect/", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Token ' . $token,
            ],
            'json' => $paymentData,
            'http_errors' => false // Pour gérer manuellement les erreurs
        ]);

        // Traitement de la réponse
        $statusCode = $response->getStatusCode();
        $responseBody = json_decode($response->getBody(), true);

        if ($statusCode !== 200) {
            // return response()->json([
            //     'success' => false,
            //     'error' => $responseBody,
            //     'message' => 'Erreur lors de l\'initialisation du paiement',
            //     ]
            // );
            return null;
        }

        $checkpayment = $this->checkTransactionStatus($token, $responseBody["reference"]);

        return $checkpayment;

    } catch (RequestException $e) {
        // Erreur réseau ou serveur
        // return response()->json([
        //     'error' => 'Erreur de connexion au service de paiement',
        //     'details' => $e->getMessage()
        // ], 500);
        dd($e);
        
    } catch (\Exception $e) {
        // Erreurs métier
        // return response()->json([
        //     'error' => $e->getMessage()
        // ], 400);
        dd($e);
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
            $response = $this->client->get("{$this->baseUrl}transaction/{$reference}", [ // Utilisez une propriété pour l'URL de paiement
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Token ' . $token,
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

            // return [
            //     'error' => false,
            //     'message' => $errorDetails,
            //     'status_code' => $response->getStatusCode(),
            // ];
            dd($e);
        }
    }
}
