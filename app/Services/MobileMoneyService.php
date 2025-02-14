<?php
 
namespace App\Services;

use GuzzleHttp\Client;

class MobileMoneyService
{
    protected $client;
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('MOBILE_MONEY_API_KEY');
        $this->baseUrl = env('MOBILE_MONEY_BASE_URL');
    }

    //** Initier le paiement */
    public function initiatePayment($phone, $amount, $transactionId)
    {
        $response = $this->client->post("{$this->baseUrl}/initiate-payment", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'phone_number' => $phone,
                'amount' => $amount,
                'transaction_id' => $transactionId,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**Pour checked le status du paiement */
    public function checkPaymentStatus($transactionId)
    {
        $response = $this->client->get("{$this->baseUrl}/payment-status", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
            ],
            'query' => [
                'transaction_id' => $transactionId,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}
