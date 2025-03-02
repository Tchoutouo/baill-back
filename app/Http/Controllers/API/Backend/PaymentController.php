<?php

namespace App\Http\Controllers\API\Backend;

use Illuminate\Http\Request;
use App\Repositories\Backend\PaiementRepository;
use App\Repositories\Backend\AnnonceRepository;
use App\Services\MobileMoneyService;

class PaymentController extends \App\Http\Controllers\Controller
{

    protected $annonceRepository;
    protected $mobileMoney;

    public function __construct(AnnonceRepository $annonceRepository, MobileMoneyService $mobileMoney)
    {
        $this->annonceRepository = $annonceRepository;
        $this->mobileMoney = $mobileMoney;
    }


    
    public function initiatePayment(Request $request)
    {
        $phoneNumber = $request->input('phone_number');
        $amount = $request->input('amount');
        $transactionId = uniqid('txn_', true); // Génere Id de transaction unique qui commence pas txn

        $response = $this->mobileMoney->initiatePayment($phoneNumber, $amount, $transactionId);

        return response()->json($response);
    }

    public function checkPaymentStatus(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $response = $this->mobileMoney->checkTransactionStatus($transactionId);

        return response()->json($response);
    }

}

