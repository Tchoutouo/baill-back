<?php

namespace App\Repositories\Backend;
use App\Models\Paiement;
use App\Repositories\ResourcesRepository;
use \Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            $paiement->montant = $data["amount"];
            $paiement->date_paiement = Carbon::now()->format('Y-m-d');
            $paiement->number = $data["number"];
            $paiement->user_id = $data["user_id"];
            $paiement->abonnement_id = $data["abonnement_id"];
            if (isset($data["reference"])) {
                $paiement->reference = $data["reference"];
            }
            $paiement->save();
            
            return $paiement;
        } catch (Exception $e) {
            \Log::error('Erreur lors de la création du paiement methode created: ' . $e->getMessage());
        }
    }

    /** Chiffre d'affaires */
    public function ChiffreAffaire(){
        return $this->model->where('statut', 2)->sum('montant');
    }

    public function updated($id,$reference,$statut){
        $paiement = $this->model->where('id', $id)->first();
        $paiement->statut = $statut;
        if($reference){
            $paiement->reference = $reference;
        }
        $paiement->save();
        return true;
    }

}
