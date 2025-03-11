<?php

namespace App\Repositories\Backend;
use App\Models\Paiement;
use App\Repositories\ResourcesRepository;
use \Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Exception;

class PaiementRepository   extends ResourcesRepository
{

    public function __construct(Paiement $paiement) {
        $this->model = $paiement;
    }

    public function getAll() {
        $paiement = $this->model->all();
        return $paiement;
    }

    public function getById($id) {
        $paiement = $this->model->where('id', $id)->first();
        return $paiement;
    }

    public function check_account($id){
        $paiement = $this->model->where('id', $id)->first()->price;
        return $paiement;
    }


    /**save paiement */
    public function created($data) {
        try {
            //code...
            $paiement = $this->model;
            $paiement->mode_paiement = $data["mode_paiement"];
            $paiement->montant = $data["montant"];
            $paiement->date_paiement = $data["date_paiement"];
            $paiement->number = $data["number"];
            $paiement->user_id = $data["user_id"];
            $paiement->abonnement_id = $data["abonnement_id"];
            $paiement->save();
            
            return true;
        } catch (Exception $e) {
            dd($e);
        }
    }

    /** Chiffre d'affaires */
    public function ChiffreAffaire(){
        return $this->model->sum('montant');
    }

}
