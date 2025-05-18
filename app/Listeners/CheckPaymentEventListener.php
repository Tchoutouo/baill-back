<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use App\Events\CheckPaymentMobile;
use App\Repositories\Backend\PaiementRepository;
use App\Repositories\Backend\AnnonceRepository;
use App\Services\MobileMoneyService;
use App\Repositories\Backend\AbonnementRepository;
use App\Jobs\TestJob;

class CheckPaymentEventListener
{
    use InteractsWithQueue;
    protected $appKey;
    protected $secretKey;
    protected $baseUrl;
    protected $client;
    protected $annonceRepository;
    protected $mobileMoney;
    protected $paiementRepository;
    protected $abonnementRepository;
    

    public $tries = 5; // Nombre max de tentatives
    public $backoff = [10, 30, 60]; // Délais (en secondes) entre les tentatives
    /**
     * Create the event listener.
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
     * Handle the event.
     */

        
    public function handle(CheckPaymentMobile $event)
    {
        try {
            $data = $event->data;
            
            if ($data['status'] === 'FAILED') {
                $status = 1;
            }else{
                $status = 2;
            }
        } catch (\Exception $e) {
            Log::error("Échec vérification paiement {}: " . $e->getMessage());
            $this->release(10); // Réessai après 10 secondes en cas d'erreur
        }
    }
}
