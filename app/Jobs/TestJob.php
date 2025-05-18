<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Events\CheckPaymentMobile;
use App\Repositories\Backend\PaiementRepository;
use App\Repositories\Backend\AnnonceRepository;
use App\Services\MobileMoneyService;
use App\Repositories\Backend\AbonnementRepository;
use GuzzleHttp\Client;

class TestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $appKey;
    protected $secretKey;
    protected $baseUrl;
    protected $client;
    protected $annonceRepository;
    protected $mobileMoney;
    protected $paiementRepository;
    protected $abonnementRepository;
    /**
     * Create a new job instance.
     */
    public function __construct(AnnonceRepository $annonceRepository, MobileMoneyService $mobileMoney, PaiementRepository $paiementRepository, AbonnementRepository $abonnementRepository)
    {
        //
        $this->appKey = env('APPKEY_FREEMOPAY');
        $this->secretKey = env('SECRETKEY_FREEMOPAY');
        $this->baseUrl = env('FREEMOPAY_BASE_URL');
        $this->client = new Client();
        $this->annonceRepository = $annonceRepository;
        $this->mobileMoney = $mobileMoney;
        $this->paiementRepository = $paiementRepository;
        $this->abonnementRepository = $abonnementRepository;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        //
        $StarTime = now()->diffInSeconds($event->elapsedTime);
        // dd("zouk",$event->elapsedTime);
        if ($StarTime >= 300) {  // 300 secondes = 5 minutes
            Log::info("⏰ Temps écoulé pour le paiement ID: {$event->reference}");
            return;
        }
        $response = $this->client->get("{$this->baseUrl}api/v2/payment/{$event->reference}", [ // Utilisez une propriété pour l'URL de paiement
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $event->token,
            ],
            'http_errors' => false // Pour gérer manuellement les erreurs
        ]);
        
        $responseBody = json_decode($response->getBody(), true);
        $responseBody["token"] = $event->token;
        
        if ($responseBody['status'] && $responseBody['status']==="SUCCESSFULL") {
            $typeAbonnement = $this->abonnementRepository->getById($response['abonnement_id'])->name;
            $nameAnnonce = $this->annonceRepository->getById($response['annonce_id'])->title;
            $this->annonceRepository->mailPaiment(env('mail_username'),$nameAnnonce, $response['amount'], $response['mode_paiement'], $typeAbonnement);
        }
    }
}
