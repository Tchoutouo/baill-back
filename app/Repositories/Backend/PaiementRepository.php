<?php

namespace App\Repositories\Backend;
use App\Models\Paiement;
use App\Repositories\ResourcesRepository;
use \Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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
    public function created($data = array()) {
        
        $paiement = $this->model->create($data);

        return $paiement;
    }

    /** Chiffre d'affaires */
    public function ChiffreAffaire(){
        return $this->model->sum('montant');
    }

}
