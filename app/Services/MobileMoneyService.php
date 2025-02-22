<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class MobileMoneyService
{
    protected $client;
    protected $baseUrl;
    protected $apiKey;
    protected $callbackUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = env('FREEMOPAY_BASE_URL');
        $this->apiKey = env('FREEMOPAY_API_KEY');
        $this->callbackUrl = env('FREEMOPAY_CALLBACK_URL');
    }

    /**
     * Initier un paiement Mobile Money.
     *
     * @param string $phoneNumber
     * @param float $amount
     * @param string $transactionId
     * @return array
     */
    public function initiatePayment($phoneNumber, $amount, $transactionId)
    {
        try {
            $response = $this->client->get("{$this->baseUrl}/initiate-payment", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'phone_number' => $phoneNumber,
                    'amount' => $amount,
                    'transaction_id' => $transactionId,
                    'callback_url' => $this->callbackUrl,
                ],
            ]);
            dump($transactionId);
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            // Gérer l'erreur
            $response = $e->getResponse();
            $errorDetails = json_decode($response->getBody()->getContents(), true);

            return [
                'error' => true,
                'message' => $errorDetails['message'] ?? 'Erreur lors de la requête API',
                'status_code' => $response->getStatusCode(),
            ];
        }
    }

    /**
     * Vérifier le statut d'une transaction.
     *
     * @param string $transactionId
     * @return array
     */
    public function checkTransactionStatus($transactionId)
    {
        try {
            $response = $this->client->get("{$this->baseUrl}/transaction-status/{$transactionId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ],
            ]);
            // dd("bien",$response);
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            // Gérer l'erreur
            $response = $e->getResponse();
            $errorDetails = json_decode($response->getBody()->getContents(), true);

            return [
                'error' => true,
                'message' => $errorDetails['message'] ?? 'Erreur lors de la requête API',
                'status_code' => $response->getStatusCode(),
            ];
        }
    }
}
